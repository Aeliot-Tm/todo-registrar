<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Comment;

use Aeliot\TodoRegistrar\Service\Comment\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
final class DetectorTest extends TestCase
{
    public static function getDataForTestDetection(): iterable
    {
        yield [
            '// TODO single line comment',
            __DIR__ . '/../../../fixtures/single_line.php',
        ];

        yield [
            <<<CONT
/*
 * Multi line comment
 */
CONT,
            __DIR__ . '/../../../fixtures/multi_line_comment.php',
        ];

        yield [
            <<<CONT
/**
 * Multi line doc block
 */
CONT,
            __DIR__ . '/../../../fixtures/multi_line_doc_block.php',
        ];
    }

    #[DataProvider('getDataForTestDetection')]
    public function testDetection(string $expectedText, string $path): void
    {
        $tokens = $this->getTokens($path);
        self::assertCount(1, $tokens);
        self::assertSame($expectedText, $tokens[0]->text);
    }

    /**
     * @return \PhpToken[]
     */
    private function getTokens(string $path): array
    {
        $tokens = \PhpToken::tokenize(file_get_contents($path));
        $tokens = (new Detector())->filter($tokens);

        return array_values($tokens);
    }
}