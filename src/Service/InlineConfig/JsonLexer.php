<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Dto\InlineConfig\Token;
use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;

/**
 * Inspired by package "doctrine/annotations"
 *
 * @implements \Iterator<Token>
 */
final class JsonLexer implements \Iterator, \Countable
{
    public const T_NONE = 1;
    public const T_STRING = 2;

    public const T_KEY = 50;

    // All symbol-tokens should be >= 1000 (1000 + decimal code of symbol in ASCII Table)
    public const T_COMMA = 1044;
    public const T_COLON = 1058;
    public const T_AT = 1064;
    public const T_SQUARE_BRACKET_OPEN = 1091;
    public const T_SQUARE_BRACKET_CLOSE = 1093;
    public const T_CURLY_BRACES_OPEN = 1123;
    public const T_CURLY_BRACES_CLOSE = 1125;

    /** @var array<string, self::T*> */
    private const TYPE_MAP = [
        ',' => self::T_COMMA,
        ':' => self::T_COLON,
        '[' => self::T_SQUARE_BRACKET_OPEN,
        ']' => self::T_SQUARE_BRACKET_CLOSE,
        '{' => self::T_CURLY_BRACES_OPEN,
        '}' => self::T_CURLY_BRACES_CLOSE,
    ];

    private int $position = 0;
    /**
     * @var Token[]
     */
    private array $tokens = [];

    public function __construct(string $input, int $offset = 0)
    {
        $this->scan($input, $offset);
    }

    public function count(): int
    {
        return \count($this->tokens);
    }

    public function current(): Token
    {
        if (!$this->valid()) {
            throw new \BadMethodCallException('Cannot get value of invalid lexer iterator');
        }

        return $this->tokens[$this->position];
    }

    public function key(): int
    {
        if (!$this->valid()) {
            throw new \BadMethodCallException('Cannot get position of invalid lexer iterator');
        }

        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @internal
     */
    public function predecessor(): ?Token
    {
        if (!$this->valid()) {
            throw new \BadMethodCallException('Cannot get value of invalid lexer iterator');
        }

        return $this->tokens[$this->position - 1] ?? null;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->tokens[$this->position]);
    }

    private function checkGap(Token $current, Token $predecessor, string $input): void
    {
        $nextPosition = $current->getPosition();
        $endPosition = $predecessor->getPosition() + mb_strlen($predecessor->getValue());

        if ($nextPosition > $endPosition) {
            $gap = substr($input, $endPosition, $nextPosition - $endPosition);
            if ('' !== trim($gap)) {
                throw new InvalidInlineConfigFormatException('Only spaces permitted between tokens');
            }
        }
    }

    /**
     * @return array<array{0: string, 1: int}>
     */
    private function getMatches(string $input, int $offset): array
    {
        $regex = sprintf(
            '/(%s)/%s',
            implode('|', $this->getRegexCatchablePatterns()),
            $this->getRegexModifiers(),
        );

        $flags = PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE;
        if (false === preg_match_all($regex, $input, $matches, $flags, $offset)) {
            throw new InvalidInlineConfigFormatException('Cannot match config tokens');
        }

        array_shift($matches);
        $matches = array_shift($matches);
        if ($matches) {
            usort($matches, static fn (array $a, array $b): int => $a[1] <=> $b[1]);
            $matches = array_values($matches);
        } else {
            throw new InvalidInlineConfigFormatException('No one token matched');
        }

        return $matches;
    }

    /**
     * @return string[]
     */
    private function getRegexCatchablePatterns(): array
    {
        return [
            '[,:\[\]\{\}]',
            '[0-9a-z_-]+',
        ];
    }

    private function getRegexModifiers(): string
    {
        return 'iu';
    }

    private function getType(?string $value): int
    {
        if (null === $value) {
            return self::T_NONE;
        }

        return self::TYPE_MAP[$value] ?? self::T_STRING;
    }

    private function scan(string $input, int $offset): void
    {
        $matches = $this->getMatches($input, $offset);

        /** @var Token[] $tokens */
        $tokens = [];
        $predecessor = null;

        foreach ($matches as $index => [$value, $position]) {
            $type = $this->getType($value);
            $nextMatch = $matches[$index + 1] ?? null;
            if ($nextMatch && self::T_STRING === $type && self::T_COLON === $this->getType($nextMatch[0])) {
                $type = self::T_KEY;
            }

            $tokens[] = $current = new Token($value, $type, $position);

            if ($predecessor) {
                $this->checkGap($current, $predecessor, $input);
            }

            $predecessor = $current;
        }

        $this->tokens = array_values($tokens);
    }
}
