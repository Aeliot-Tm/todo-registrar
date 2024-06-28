<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\IndexedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\NamedCollection;
use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;

final class ArrayFromJsonLexerBuilder
{
    /**
     * @return array<array-key,mixed>
     */
    public function build(JsonLexer $lexer): array
    {
        $lexer->rewind();
        if (JsonLexer::T_CURLY_BRACES_OPEN !== $lexer->current()->getType()) {
            throw new InvalidInlineConfigFormatException('Config must be started with curly braces');
        }

        $lexer->next();

        $collection = new NamedCollection();
        $level = 1;

        $this->populate($lexer, $collection, $level);

        if (0 !== $level) {
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
     * TODO: move it into JsonLexer
     */
    private function checkPredecessorType(int $current, ?int $predecessor): void
    {
        if (JsonLexer::T_COLON === $current && JsonLexer::T_KEY !== $predecessor) {
            throw new InvalidInlineConfigFormatException(
                sprintf('Colon must be after key, but passed %d', $predecessor),
            );
        }

        if (JsonLexer::T_COMMA === $current && JsonLexer::T_COMMA === $predecessor) {
            throw new InvalidInlineConfigFormatException('Duplicated comma');
        }

        if (JsonLexer::T_CURLY_BRACES_OPEN === $current
            && !(null === $predecessor || JsonLexer::T_COLON === $predecessor)
        ) {
            throw new InvalidInlineConfigFormatException('Opening curly braces may be initial or must be after colon');
        }

        if (JsonLexer::T_SQUARE_BRACKET_OPEN === $current && JsonLexer::T_COLON !== $predecessor) {
            throw new InvalidInlineConfigFormatException('Opening square bracket must be after colon');
        }

        if (JsonLexer::T_STRING === $current && JsonLexer::T_STRING === $predecessor) {
            throw new InvalidInlineConfigFormatException('Duplicated value');
        }

        if (JsonLexer::T_COLON === $predecessor
            && \in_array($current, [JsonLexer::T_CURLY_BRACES_CLOSE, JsonLexer::T_SQUARE_BRACKET_CLOSE], true)
        ) {
            throw new InvalidInlineConfigFormatException(
                'Colon cannot be before closing curly braces or closing square brackets',
            );
        }
    }

    private function isCloseCollection(Token $token): bool
    {
        return \in_array($token->getType(), [JsonLexer::T_SQUARE_BRACKET_CLOSE, JsonLexer::T_CURLY_BRACES_CLOSE], true);
    }

    private function isSkippable(Token $token): bool
    {
        return \in_array($token->getType(), [JsonLexer::T_COLON, JsonLexer::T_COMMA], true);
    }

    private function isValue(Token $token): bool
    {
        return JsonLexer::T_STRING === $token->getType();
    }

    private function populate(JsonLexer $lexer, CollectionInterface $collection, int &$level): void
    {
        $key = null;
        do {
            $token = $lexer->current();
            $this->checkPredecessorType($token->getType(), $lexer->predecessor()?->getType());
            $lexer->next();

            if (JsonLexer::T_KEY === $token->getType()) {
                $key = $token->getValue();
            } elseif ($this->isValue($token)) {
                $this->addValue($collection, $key, $token->getValue());
            } elseif (JsonLexer::T_CURLY_BRACES_OPEN === $token->getType()) {
                ++$level;
                $childCollection = new NamedCollection();
                $this->populate($lexer, $childCollection, $level);
                $this->addValue($collection, $key, $childCollection);
                $key = null;
            } elseif (JsonLexer::T_SQUARE_BRACKET_OPEN === $token->getType()) {
                ++$level;
                $childCollection = new IndexedCollection();
                $this->populate($lexer, $childCollection, $level);
                $this->addValue($collection, $key, $childCollection);
            } elseif ($this->isCloseCollection($token)) {
                --$level;
                $key = null;
            } elseif (!$this->isSkippable($token)) {
                throw new InvalidInlineConfigFormatException('Unexpected token detected');
            }
        } while ($lexer->valid());
    }
}
