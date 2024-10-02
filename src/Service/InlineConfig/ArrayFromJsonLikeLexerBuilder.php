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

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;

final class ArrayFromJsonLikeLexerBuilder
{
    /**
     * @return array<array-key,mixed>
     */
    public function build(JsonLikeLexer $lexer): array
    {
        $lexer->rewind();
        if (JsonLikeLexer::T_CURLY_BRACES_OPEN !== $lexer->current()->getType()) {
            throw new InvalidInlineConfigFormatException('Config must be started with curly braces');
        }

        $lexer->next();

        $collection = new NamedCollection();
        $level = 1;

        $this->populate($lexer, $collection, $level);

        if (0 !== $level || $lexer->valid()) {
            throw new InvalidInlineConfigFormatException('Unexpected end of tags list');
        }

        return $collection->toArray();
    }

    private function addValue(CollectionInterface $collection, ?string $key, mixed $value): void
    {
        if ($collection instanceof NamedCollection) {
            if (null === $key) {
                throw new InvalidInlineConfigFormatException('Undefined key for named collection');
            }
            $collection->add($key, $value);
        }

        if ($collection instanceof IndexedCollection) {
            if (null !== $key) {
                throw new InvalidInlineConfigFormatException('Key passed for indexed collection');
            }
            $collection->add($value);
        }
    }

    /**
     * TODO: #126 move it into JsonLikeLexer.
     */
    private function checkPredecessorType(int $current, ?int $predecessor): void
    {
        if (JsonLikeLexer::T_COLON === $current && JsonLikeLexer::T_KEY !== $predecessor) {
            $exceptionMessage = \sprintf('Colon must be after key, but passed %d', $predecessor);
            throw new InvalidInlineConfigFormatException($exceptionMessage);
        }

        if (JsonLikeLexer::T_COMMA === $current && JsonLikeLexer::T_COMMA === $predecessor) {
            throw new InvalidInlineConfigFormatException('Duplicated comma');
        }

        if (JsonLikeLexer::T_CURLY_BRACES_OPEN === $current
            && !(null === $predecessor || JsonLikeLexer::T_COLON === $predecessor)
        ) {
            throw new InvalidInlineConfigFormatException('Opening curly braces may be initial or must be after colon');
        }

        if (JsonLikeLexer::T_SQUARE_BRACKET_OPEN === $current && JsonLikeLexer::T_COLON !== $predecessor) {
            throw new InvalidInlineConfigFormatException('Opening square bracket must be after colon');
        }

        if (JsonLikeLexer::T_STRING === $current && JsonLikeLexer::T_STRING === $predecessor) {
            throw new InvalidInlineConfigFormatException('Duplicated value');
        }

        if (JsonLikeLexer::T_COLON === $predecessor
            && \in_array($current, [JsonLikeLexer::T_CURLY_BRACES_CLOSE, JsonLikeLexer::T_SQUARE_BRACKET_CLOSE], true)
        ) {
            $exceptionMessage = 'Colon cannot be before closing curly braces or closing square brackets';
            throw new InvalidInlineConfigFormatException($exceptionMessage);
        }
    }

    private function isCloseCollection(Token $token): bool
    {
        return \in_array($token->getType(), [JsonLikeLexer::T_SQUARE_BRACKET_CLOSE, JsonLikeLexer::T_CURLY_BRACES_CLOSE], true);
    }

    private function isSkippable(Token $token): bool
    {
        return \in_array($token->getType(), [JsonLikeLexer::T_COLON, JsonLikeLexer::T_COMMA], true);
    }

    private function isValue(Token $token): bool
    {
        return JsonLikeLexer::T_STRING === $token->getType();
    }

    private function populate(JsonLikeLexer $lexer, CollectionInterface $collection, int &$level): void
    {
        $key = null;
        do {
            $token = $lexer->current();
            $this->checkPredecessorType($token->getType(), $lexer->predecessor()?->getType());
            $lexer->next();

            if (JsonLikeLexer::T_KEY === $token->getType()) {
                $key = $token->getValue();
            } elseif ($this->isValue($token)) {
                $this->addValue($collection, $key, $token->getValue());
            } elseif (JsonLikeLexer::T_CURLY_BRACES_OPEN === $token->getType()) {
                ++$level;
                $childCollection = new NamedCollection();
                $this->populate($lexer, $childCollection, $level);
                $this->addValue($collection, $key, $childCollection);
            } elseif (JsonLikeLexer::T_SQUARE_BRACKET_OPEN === $token->getType()) {
                ++$level;
                $childCollection = new IndexedCollection();
                $this->populate($lexer, $childCollection, $level);
                $this->addValue($collection, $key, $childCollection);
            } elseif ($this->isCloseCollection($token)) {
                --$level;
                break;
            } elseif (!$this->isSkippable($token)) {
                throw new InvalidInlineConfigFormatException('Unexpected token detected');
            }
        } while ($lexer->valid());
    }
}
