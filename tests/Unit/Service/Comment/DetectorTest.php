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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Comment;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Comment\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
#[UsesClass(TagMetadata::class)]
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
