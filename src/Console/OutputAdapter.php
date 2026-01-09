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

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class OutputAdapter implements OutputInterface
{
    public const VERBOSITY_SILENT = OutputInterface::VERBOSITY_SILENT;
    public const VERBOSITY_QUIET = OutputInterface::VERBOSITY_QUIET;
    public const VERBOSITY_NORMAL = OutputInterface::VERBOSITY_NORMAL;
    public const VERBOSITY_VERBOSE = OutputInterface::VERBOSITY_VERBOSE;
    public const VERBOSITY_VERY_VERBOSE = OutputInterface::VERBOSITY_VERY_VERBOSE;
    public const VERBOSITY_DEBUG = OutputInterface::VERBOSITY_DEBUG;

    public function __construct(
        private OutputInterface $output,
    ) {
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
    {
        $this->output->write($messages, $newline, $options);
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function writeln(string|iterable $messages, int $options = 0): void
    {
        $this->output->writeln($messages, $options);
    }

    public function setVerbosity(int $level): void
    {
        $this->output->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    public function isSilent(): bool
    {
        return $this->output->isSilent();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->output->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->output->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->output->getFormatter();
    }

    /**
     * @param string|iterable<string> $messages
     */
    public function writeErr(string|iterable $messages, int $options = 0): void
    {
        (
            $this->output instanceof ConsoleOutputInterface
                ? $this->output->getErrorOutput()
                : $this->output
        )->writeln($messages, $options);
    }
}
