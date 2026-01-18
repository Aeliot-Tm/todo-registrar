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

namespace Aeliot\TodoRegistrar\Service\File;

use Aeliot\TodoRegistrar\Dto\Parsing\CommentNode;
use Aeliot\TodoRegistrar\Dto\Parsing\LazyContextMap;
use Aeliot\TodoRegistrar\Dto\Parsing\MappedContext;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use PhpParser\ParserFactory;

/**
 * Hybrid parser: combines tokens from Parser and AST for context mapping.
 *
 * @internal
 */
final readonly class FileParser
{
    public function parse(\SplFileInfo $file): ParsedFile
    {
        $pathname = $file->getPathname();
        $content = file_get_contents($pathname);
        if (false === $content) {
            throw new \RuntimeException(\sprintf('Cannot read file %s', $pathname));
        }

        $parser = (new ParserFactory())->createForHostVersion();
        $ast = $parser->parse($content) ?? [];
        $tokens = $this->filterEofToken($parser->getTokens());

        return new ParsedFile($file, $tokens, $this->buildCommentNodes($tokens, $ast, $pathname));
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
     * @param \PhpToken[] $tokens
     * @param array<\PhpParser\Node\Stmt> $ast
     *
     * @return CommentNode[]
     */
    private function buildCommentNodes(array $tokens, array $ast, string $filePath): array
    {
        $commentNodes = [];
        $lazyContextMap = new LazyContextMap($ast, $filePath);

        foreach ($tokens as $token) {
            if (!$this->isComment($token)) {
                continue;
            }

            $commentNodes[] = new CommentNode($token, new MappedContext($token->line, $lazyContextMap));
        }

        return $commentNodes;
    }

    private function isComment(\PhpToken $token): bool
    {
        return \in_array($token->id, [\T_COMMENT, \T_DOC_COMMENT], true);
    }
}
