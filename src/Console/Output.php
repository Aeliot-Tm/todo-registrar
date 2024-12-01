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

namespace Aeliot\TodoRegistrar\Console;

/**
 * @internal
 */
final class Output
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;

    /**
     * @var resource
     */
    private $stderr;

    /**
     * @var resource
     */
    private $stdout;

    public function __construct(private int $verbosity = self::VERBOSITY_NORMAL)
    {
        $this->stdout = $this->openOutputStream();
        $this->stderr = $this->openErrorStream();
    }

    public function isQuiet(): bool
    {
        return self::VERBOSITY_QUIET === $this->verbosity;
    }

    public function isVerbose(): bool
    {
        return self::VERBOSITY_VERBOSE <= $this->verbosity;
    }

    public function isVeryVerbose(): bool
    {
        return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
    }

    public function isDebug(): bool
    {
        return self::VERBOSITY_DEBUG <= $this->verbosity;
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function writeln($messages): void
    {
        $this->writeMessages($this->stdout, $messages);
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function writeErr($messages): void
    {
        $this->writeMessages($this->stderr, $messages);
    }

    /**
     * @param resource $stream
     * @param string|iterable<string> $messages
     */
    private function writeMessages($stream, $messages): void
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->doWrite($stream, $message, true);
        }
    }

    /**
     * @param resource $stream
     */
    private function doWrite($stream, string $message, bool $newline): void
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }

        @fwrite($stream, $message);
        fflush($stream);
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDOUT.
     */
    private function hasStdoutSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDERR.
     */
    private function hasStderrSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    /**
     * Checks if current executing environment is IBM iSeries (OS400), which
     * doesn't properly convert character-encodings between ASCII to EBCDIC.
     */
    private function isRunningOS400(): bool
    {
        $checks = [
            \function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            \PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }

    /**
     * @return resource
     */
    private function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return fopen('php://output', 'w');
        }

        // Use STDOUT when possible to prevent from opening too many file descriptors
        return \defined('STDOUT') ? \STDOUT : (@fopen('php://stdout', 'w') ?: fopen('php://output', 'w'));
    }

    /**
     * @return resource
     */
    private function openErrorStream()
    {
        if (!$this->hasStderrSupport()) {
            return fopen('php://output', 'w');
        }

        // Use STDERR when possible to prevent from opening too many file descriptors
        return \defined('STDERR') ? \STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }
}
