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

namespace Aeliot\TodoRegistrar\Service\File\Parser;

use Aeliot\TodoRegistrar\AST\PHP\ContextMapBuilder;
use Aeliot\TodoRegistrar\Dto\Parsing\LazyContextMap;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\Token\PhpTokenAdapter;
use Aeliot\TodoRegistrar\Dto\Token\TokenInterface;
use Aeliot\TodoRegistrar\Exception\FileReadException;
use Aeliot\TodoRegistrar\Service\File\FileParserInterface;
use PhpParser\ParserFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Hybrid parser: combines tokens from Parser and AST for context mapping.
 *
 * @internal
 */
#[AsTaggedItem(index: 'php')]
final readonly class PhpFileParser implements FileParserInterface
{
    /**
     * @throws FileReadException
     */
    public function parse(\SplFileInfo $file): ParsedFile
    {
        $pathname = $file->getPathname();
        $content = file_get_contents($pathname);
        if (false === $content) {
            throw new FileReadException(\sprintf('Cannot read file %s', $pathname));
        }

        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($content) ?? [];
        $tokens = $this->wrapTokens($this->filterEofToken($parser->getTokens()));

        return new ParsedFile($file, $tokens, new LazyContextMap(new ContextMapBuilder($ast, $pathname)));
    }

    /**
     * Filter EOF token (id=0) added by parser to avoid null byte in saved file.
     *
     * @param \PhpToken[] $tokens
     *
     * @return \PhpToken[]
     */
    private function filterEofToken(array $tokens): array
    {
        return array_filter($tokens, static fn (\PhpToken $t): bool => 0 !== $t->id);
    }

    /**
     * @param \PhpToken[] $phpTokens
     *
     * @return TokenInterface[]
     */
    private function wrapTokens(array $phpTokens): array
    {
        return array_map(static fn (\PhpToken $t): TokenInterface => new PhpTokenAdapter($t), $phpTokens);
    }
}
