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

use Aeliot\TodoRegistrar\AST\YAML\ContextMapBuilder;
use Aeliot\TodoRegistrar\Dto\Parsing\LazyContextMap;
use Aeliot\TodoRegistrar\Dto\Parsing\ParsedFile;
use Aeliot\TodoRegistrar\Dto\Token\YamlTokenAdapter;
use Aeliot\TodoRegistrar\Exception\FileReadException;
use Aeliot\TodoRegistrar\Service\File\FileParserInterface;
use Aeliot\YamlToken\Node\Node;
use Aeliot\YamlToken\Node\StreamNode;
use Aeliot\YamlToken\Node\TokenHolderInterface;
use Aeliot\YamlToken\Parser\ParserBuilder;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Hybrid parser: combines tokens from Parser and AST for context mapping.
 *
 * @internal
 */
#[AsTaggedItem(index: 'yaml')]
#[AsTaggedItem(index: 'yml')]
final readonly class YamlFileParser implements FileParserInterface
{
    public function parse(\SplFileInfo $file): ParsedFile
    {
        $pathname = $file->getPathname();
        $content = file_get_contents($pathname);
        if (false === $content) {
            throw new FileReadException(\sprintf('Cannot read file %s', $pathname));
        }

        $parser = (new ParserBuilder())->createParser();
        $streamNode = $parser->parse($content);
        $tokens = $this->wrapTokens($streamNode);

        return new ParsedFile($file, $tokens, new LazyContextMap(new ContextMapBuilder($streamNode, $pathname)));
    }

    /**
     * @return YamlTokenAdapter[]
     */
    private function wrapTokens(StreamNode $streamNode): array
    {
        return array_map(
            static fn (Node&TokenHolderInterface $t): YamlTokenAdapter => new YamlTokenAdapter($t),
            $this->walkNode($streamNode),
        );
    }

    /**
     * @return array<Node&TokenHolderInterface>
     */
    private function walkNode(Node $node): array
    {
        if ($node instanceof TokenHolderInterface) {
            return [$node];
        }

        $nodes = [];
        foreach ($node->getChildren() as $child) {
            $nodes[] = $this->walkNode($child);
        }

        return $nodes ? array_merge(...$nodes) : [];
    }
}
