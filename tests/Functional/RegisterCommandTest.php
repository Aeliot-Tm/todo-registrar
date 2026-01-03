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

use Aeliot\TodoRegistrar\Test\Stub\StubRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
#[Large]
final class RegisterCommandTest extends TestCase
{
    private const ORIGINAL_CONTENT = "<?php\n\n// TODO: Test task description\n";
    private const TICKET_KEY_ENV_VAR_NAME = 'REGISTER_COMMAND_TEST_TICKET_KEY';

    private ?string $originalTicketKeyEnvValue = null;
    private string $tempDir;
    private string $testFile;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/todo-registrar-test-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);

        $this->testFile = $this->tempDir . '/test_file.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            file_put_contents($this->testFile, self::ORIGINAL_CONTENT);
        }

        if (isset($this->originalTicketKeyEnvValue)) {
            $_ENV[self::TICKET_KEY_ENV_VAR_NAME] = $this->originalTicketKeyEnvValue;
            putenv(self::TICKET_KEY_ENV_VAR_NAME . '=' . $this->originalTicketKeyEnvValue);
        } else {
            unset($_ENV[self::TICKET_KEY_ENV_VAR_NAME]);
            putenv(self::TICKET_KEY_ENV_VAR_NAME);
        }

        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public static function configProvider(): iterable
    {
        yield 'PHP config' => ['php'];
        yield 'YAML config' => ['yaml'];
    }

    #[DataProvider('configProvider')]
    public function testRegisterWithStubRegistrar(string $configType): void
    {
        $expectedTicketKey = 'TEST-456';
        $configFile = $this->createConfigFile($configType, $expectedTicketKey);
        file_put_contents($this->testFile, self::ORIGINAL_CONTENT);

        if ('yaml' === $configType) {
            $this->setTicketKeyEnvironmentVariable($expectedTicketKey);
        }

        $exitCode = $this->runTodoRegistrar($configFile);

        self::assertSame(0, $exitCode, 'Script should exit with code 0');

        $modifiedContent = file_get_contents($this->testFile);
        self::assertStringContainsString(
            "// TODO: {$expectedTicketKey} Test task description",
            $modifiedContent,
            'Comment should contain ticket key',
        );
    }

    public function testRegisterWithMissingEnvVarThrowsException(): void
    {
        $configFile = $this->createYamlConfig();
        file_put_contents($this->testFile, self::ORIGINAL_CONTENT);

        $this->unsetTicketKeyEnvironmentVariable();

        [$exitCode, $output] = $this->runTodoRegistrarWithOutput($configFile);

        self::assertNotSame(0, $exitCode, 'Script should exit with non-zero code when env var is missing');
        $outputString = implode("\n", $output);
        self::assertStringContainsString(
            'Undefined environment variable',
            $outputString,
            'Error message should mention undefined environment variable',
        );
        self::assertStringContainsString(
            self::TICKET_KEY_ENV_VAR_NAME,
            $outputString,
            'Error message should contain environment variable name',
        );
    }

    public function testRegisterWithStdinConfig(): void
    {
        $expectedTicketKey = 'TEST-789';
        file_put_contents($this->testFile, self::ORIGINAL_CONTENT);

        $this->setTicketKeyEnvironmentVariable($expectedTicketKey);

        $yamlContent = $this->buildYamlConfigContent();
        $exitCode = $this->runTodoRegistrarWithStdin($yamlContent);

        self::assertSame(0, $exitCode, 'Script should exit with code 0');

        $modifiedContent = file_get_contents($this->testFile);
        self::assertStringContainsString(
            "// TODO: {$expectedTicketKey} Test task description",
            $modifiedContent,
            'Comment should contain ticket key',
        );
    }

    public function testRegisterWithStdinConfigMissingEnvVarThrowsException(): void
    {
        file_put_contents($this->testFile, self::ORIGINAL_CONTENT);

        $this->unsetTicketKeyEnvironmentVariable();

        $yamlContent = $this->buildYamlConfigContent();
        [$exitCode, $output] = $this->runTodoRegistrarWithStdinAndOutput($yamlContent);

        self::assertNotSame(0, $exitCode, 'Script should exit with non-zero code when env var is missing');
        $outputString = implode("\n", $output);
        self::assertStringContainsString(
            'Undefined environment variable',
            $outputString,
            'Error message should mention undefined environment variable',
        );
        self::assertStringContainsString(
            self::TICKET_KEY_ENV_VAR_NAME,
            $outputString,
            'Error message should contain environment variable name',
        );
    }

    private function createConfigFile(string $type, string $ticketKey): string
    {
        if ('php' === $type) {
            return $this->createPhpConfig($ticketKey);
        }

        return $this->createYamlConfig();
    }

    private function createPhpConfig(string $ticketKey): string
    {
        $configFile = $this->tempDir . '/.todo-registrar.php';
        $stubFactoryClass = StubRegistrarFactory::class;
        $configContent = <<<PHP
<?php

declare(strict_types=1);

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder((new Finder())->in('{$this->tempDir}'))
    ->setRegistrar('{$stubFactoryClass}', [
        'ticket_key' => '{$ticketKey}',
    ]);

PHP;
        file_put_contents($configFile, $configContent);

        return $configFile;
    }

    private function createYamlConfig(): string
    {
        $configFile = $this->tempDir . '/.todo-registrar.yaml';
        $configContent = $this->buildYamlConfigContent();
        file_put_contents($configFile, $configContent);

        return $configFile;
    }

    private function buildYamlConfigContent(): string
    {
        $stubFactoryClass = StubRegistrarFactory::class;
        $ticketKeyEnvVar = self::TICKET_KEY_ENV_VAR_NAME;

        return <<<YAML
paths:
  in: {$this->tempDir}

registrar:
  type: {$stubFactoryClass}
  options:
    ticket_key: '%env({$ticketKeyEnvVar})%'

tags:
  - todo

YAML;
    }

    private function setTicketKeyEnvironmentVariable(string $value): void
    {
        $this->originalTicketKeyEnvValue = $_ENV[self::TICKET_KEY_ENV_VAR_NAME] ?? null;
        $_ENV[self::TICKET_KEY_ENV_VAR_NAME] = $value;
        putenv(self::TICKET_KEY_ENV_VAR_NAME . '=' . $value);
    }

    private function unsetTicketKeyEnvironmentVariable(): void
    {
        $this->originalTicketKeyEnvValue = $_ENV[self::TICKET_KEY_ENV_VAR_NAME] ?? null;
        unset($_ENV[self::TICKET_KEY_ENV_VAR_NAME]);
        putenv(self::TICKET_KEY_ENV_VAR_NAME);
    }

    private function runTodoRegistrar(string $configFile): int
    {
        $projectRoot = \dirname(__DIR__, 2);
        $scriptPath = $projectRoot . '/bin/todo-registrar';
        $command = \sprintf(
            '%s %s --config=%s 2>&1',
            escapeshellarg(\PHP_BINARY),
            escapeshellarg($scriptPath),
            escapeshellarg($configFile)
        );

        $output = [];
        $exitCode = 0;
        $originalCwd = getcwd();
        try {
            chdir($projectRoot);
            exec($command, $output, $exitCode);
        } finally {
            chdir($originalCwd);
        }

        return $exitCode;
    }

    /**
     * @return array{0: int, 1: string[]}
     */
    private function runTodoRegistrarWithOutput(string $configFile): array
    {
        $projectRoot = \dirname(__DIR__, 2);
        $scriptPath = $projectRoot . '/bin/todo-registrar';
        $command = \sprintf(
            '%s %s --config=%s 2>&1',
            escapeshellarg(\PHP_BINARY),
            escapeshellarg($scriptPath),
            escapeshellarg($configFile)
        );

        $output = [];
        $exitCode = 0;
        $originalCwd = getcwd();
        try {
            chdir($projectRoot);
            exec($command, $output, $exitCode);
        } finally {
            chdir($originalCwd);
        }

        return [$exitCode, $output];
    }

    private function runTodoRegistrarWithStdin(string $yamlContent): int
    {
        [$exitCode] = $this->runTodoRegistrarWithStdinAndOutput($yamlContent);

        return $exitCode;
    }

    /**
     * @return array{0: int, 1: string[]}
     */
    private function runTodoRegistrarWithStdinAndOutput(string $yamlContent): array
    {
        $projectRoot = \dirname(__DIR__, 2);
        $scriptPath = $projectRoot . '/bin/todo-registrar';
        $command = \sprintf(
            '%s %s --config=STDIN 2>&1',
            escapeshellarg(\PHP_BINARY),
            escapeshellarg($scriptPath)
        );

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $originalCwd = getcwd();
        try {
            chdir($projectRoot);
            $process = proc_open($command, $descriptorSpec, $pipes);
            if (!\is_resource($process)) {
                throw new \RuntimeException('Failed to open process');
            }

            fwrite($pipes[0], $yamlContent);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
        } finally {
            chdir($originalCwd);
        }

        $outputLines = [];
        if ($output) {
            $outputLines = explode("\n", $output);
        }
        if ($error) {
            $errorLines = explode("\n", $error);
            $outputLines = array_merge($outputLines, $errorLines);
        }

        return [$exitCode, $outputLines];
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
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
