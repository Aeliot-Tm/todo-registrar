<?php

declare(strict_types=1);

/*
 * This file is part of the TODO Registrar project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\TodoRegistrar\Test\Functional;

use Aeliot\TodoRegistrar\Test\Stub\IncrementalRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
#[Large]
final class GlueSameTicketsTest extends TestCase
{
    private const TODO_SAME = "<?php\n\n// TODO: Fix the payment bug\n";
    private const TODO_DIFFERENT = "<?php\n\n// TODO: Unrelated task\n";

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/todo-registrar-glue-test-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testGlueSameTicketsEnabled(): void
    {
        file_put_contents($this->tempDir . '/a.php', self::TODO_SAME);
        file_put_contents($this->tempDir . '/b.php', self::TODO_SAME);
        file_put_contents($this->tempDir . '/c.php', self::TODO_DIFFERENT);

        $configFile = $this->createPhpConfig(true);
        $this->runTodoRegistrar($configFile);

        self::assertStringContainsString(
            '// TODO: KEY-1 Fix the payment bug',
            (string) file_get_contents($this->tempDir . '/a.php'),
            'First identical TODO should get KEY-1',
        );
        self::assertStringContainsString(
            '// TODO: KEY-1 Fix the payment bug',
            (string) file_get_contents($this->tempDir . '/b.php'),
            'Second identical TODO should reuse KEY-1',
        );
        self::assertStringContainsString(
            '// TODO: KEY-2 Unrelated task',
            (string) file_get_contents($this->tempDir . '/c.php'),
            'Different TODO should get KEY-2',
        );
    }

    public function testGlueSameTicketsDisabled(): void
    {
        file_put_contents($this->tempDir . '/a.php', self::TODO_SAME);
        file_put_contents($this->tempDir . '/b.php', self::TODO_SAME);
        file_put_contents($this->tempDir . '/c.php', self::TODO_DIFFERENT);

        $configFile = $this->createPhpConfig(false);
        $this->runTodoRegistrar($configFile);

        $contentA = (string) file_get_contents($this->tempDir . '/a.php');
        $contentB = (string) file_get_contents($this->tempDir . '/b.php');
        $contentC = (string) file_get_contents($this->tempDir . '/c.php');

        self::assertStringContainsString(
            '// TODO: KEY-1 Fix the payment bug',
            $contentA,
            'First TODO should get KEY-1',
        );
        self::assertStringContainsString(
            '// TODO: KEY-2 Fix the payment bug',
            $contentB,
            'Second identical TODO should get its own KEY-2 when gluing is disabled',
        );
        self::assertStringContainsString(
            '// TODO: KEY-3 Unrelated task',
            $contentC,
            'Third TODO should get KEY-3',
        );
    }

    private function createPhpConfig(bool $glueSameTickets): string
    {
        $configFile = $this->tempDir . '/.todo-registrar.php';
        $stubFactoryClass = IncrementalRegistrarFactory::class;
        $glueSameTicketsStr = $glueSameTickets ? 'true' : 'false';

        $configContent = <<<PHP
<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use Aeliot\TodoRegistrar\Service\File\Finder;

\$processConfig = new ProcessConfig();
\$processConfig->setGlueSameTickets({$glueSameTicketsStr});

return (new Config())
    ->setFinder((new Finder())->in('{$this->tempDir}'))
    ->setRegistrar('{$stubFactoryClass}', ['prefix' => 'KEY'])
    ->setProcessConfig(\$processConfig);

PHP;

        file_put_contents($configFile, $configContent);

        return $configFile;
    }

    private function runTodoRegistrar(string $configFile): void
    {
        $projectRoot = \dirname(__DIR__, 2);
        $scriptPath = $projectRoot . '/bin/todo-registrar';
        $command = \sprintf(
            '%s %s --config=%s 2>&1',
            escapeshellarg(\PHP_BINARY),
            escapeshellarg($scriptPath),
            escapeshellarg($configFile),
        );

        $originalCwd = getcwd();
        try {
            chdir($projectRoot);
            exec($command, $output, $exitCode);
        } finally {
            chdir((string) $originalCwd);
        }

        self::assertSame(0, $exitCode, 'Script should exit with code 0. Output: ' . implode("\n", $output));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff((array) scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
