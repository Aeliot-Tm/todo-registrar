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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\File;

use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Saver::class)]
#[CoversClass(Tokenizer::class)]
final class TokenizerAndSaverTest extends TestCase
{
    /**
     * @return iterable<array{0: string}>
     */
    public static function getDataForTestTokenizeAndSave(): iterable
    {
        yield [__DIR__ . '/../../../fixtures/single_line.php'];
        yield [__DIR__ . '/../../../fixtures/multi_line_comment.php'];
        yield [__DIR__ . '/../../../fixtures/multi_line_doc_block.php'];
    }

    #[DataProvider('getDataForTestTokenizeAndSave')]
    public function testTokenizeAndSave($incomingPathame): void
    {
        $outgoingPathname = sys_get_temp_dir() . '/tr-' . microtime(true) . '-' . mt_rand() . '.php';

        $tokens = (new Tokenizer())->tokenize($this->getMockSplFileInfo($incomingPathame));
        (new Saver())->save($this->getMockSplFileInfo($outgoingPathname), $tokens);

        self::assertFileEquals($incomingPathame, $outgoingPathname);
    }

    private function getMockSplFileInfo(string $pathname): \SplFileInfo&MockObject
    {
        $incomeFile = $this->createMock(\SplFileInfo::class);
        $incomeFile->method('getPathname')->willReturn($pathname);

        return $incomeFile;
    }
}
