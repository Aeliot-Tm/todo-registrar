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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Report;

use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Enum\ReportFormat;
use Aeliot\TodoRegistrar\Service\Report\ReportBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReportBuilder::class)]
final class ReportBuilderTest extends TestCase
{
    private ReportBuilder $reportBuilder;

    protected function setUp(): void
    {
        $this->reportBuilder = new ReportBuilder();
    }

    public function testFormatWithNoneThrowsException(): void
    {
        $statistic = new ProcessStatistic();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Report format "none" cannot be used for formatting');

        $this->reportBuilder->format(ReportFormat::NONE, $statistic);
    }

    public function testFormatWithJsonReturnsValidJson(): void
    {
        $statistic = $this->createStatistic(
            ['src/foo.php' => 2, 'src/bar.php' => 3],
            1,
            1,
            10
        );

        $result = $this->reportBuilder->format(ReportFormat::JSON, $statistic);

        $decoded = json_decode($result, true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('summary', $decoded);
        self::assertArrayHasKey('files', $decoded);
        self::assertSame(2, $decoded['summary']['files']['analyzed']);
        self::assertSame(2, $decoded['summary']['files']['updated']);
        self::assertSame(1, $decoded['summary']['todos']['ignored']);
        self::assertSame(1, $decoded['summary']['todos']['glued']);
        self::assertSame(5, $decoded['summary']['todos']['registered']);
        self::assertSame(7, $decoded['summary']['todos']['total']);
        self::assertSame(10, $decoded['summary']['comments']['detected']);
        self::assertCount(2, $decoded['files']);
        self::assertSame('src/foo.php', $decoded['files'][0]['path']);
        self::assertSame(2, $decoded['files'][0]['summary']['todos']['registered']);
        self::assertSame('src/bar.php', $decoded['files'][1]['path']);
        self::assertSame(3, $decoded['files'][1]['summary']['todos']['registered']);
    }

    public function testFormatWithYamlReturnsValidYaml(): void
    {
        $statistic = $this->createStatistic(['a.php' => 2, 'b.php' => 3], 0, 0, 100);

        $result = $this->reportBuilder->format(ReportFormat::YAML, $statistic);

        self::assertStringContainsString('summary:', $result);
        self::assertStringContainsString('files:', $result);
        self::assertStringContainsString('analyzed: 2', $result);
        self::assertStringContainsString('path: a.php', $result);
    }

    public function testFormatIncludesAllFilesIncludingWithZeroRegistrations(): void
    {
        $statistic = $this->createStatistic(['updated.php' => 2, 'empty.php' => 0], 0, 0, 0);

        $result = $this->reportBuilder->format(ReportFormat::JSON, $statistic);

        $decoded = json_decode($result, true);
        self::assertCount(2, $decoded['files']);
        $paths = array_column($decoded['files'], 'path');
        self::assertContains('updated.php', $paths);
        self::assertContains('empty.php', $paths);
        $filesByPath = array_column($decoded['files'], null, 'path');
        self::assertSame(2, $filesByPath['updated.php']['summary']['todos']['registered']);
        self::assertSame(0, $filesByPath['empty.php']['summary']['todos']['registered']);
    }

    /**
     * @param array<string, int> $files path => registration count
     */
    private function createStatistic(array $files, int $ignored, int $glued, int $commentTokens): ProcessStatistic
    {
        $statistic = new ProcessStatistic();

        for ($i = 0; $i < $commentTokens; ++$i) {
            $statistic->tickCommentToken();
        }
        for ($i = 0; $i < $ignored; ++$i) {
            $statistic->tickIgnoredTodo();
        }
        for ($i = 0; $i < $glued; ++$i) {
            $statistic->tickGluedTodo();
        }

        foreach ($files as $path => $count) {
            $statistic->markFileVisit($path);
            for ($i = 0; $i < $count; ++$i) {
                $statistic->tickRegistration($path);
            }
        }

        return $statistic;
    }
}
