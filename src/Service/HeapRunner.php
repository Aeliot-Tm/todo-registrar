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

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Service\File\Finder;

class HeapRunner
{
    public function __construct(
        private Finder $finder,
        private FileProcessor $fileProcessor,
        private OutputAdapter $output,
    ) {
    }

    public function run(): int
    {
        $result = 0;
        $totalFiles = 0;
        $totalNewTodos = 0;
        foreach ($this->finder as $file) {
            if ($this->output->isDebug()) {
                $this->output->writeln("Begin process file: {$file->getPathname()}");
            }
            try {
                $totalNewTodos += $countNewTodos = $this->fileProcessor->process($file, $this->output);
                if ($countNewTodos && $this->output->isVerbose()) {
                    $this->output->writeln("Registered $countNewTodos for file: {$file->getPathname()}");
                }
            } catch (\Throwable $exception) {
                $this->writeError($exception, $file);
                $result = 1;
            }
            ++$totalFiles;
        }

        if (!$this->output->isQuiet()) {
            $this->output->writeln("Registered $totalNewTodos for $totalFiles files");
        }

        return $result;
    }

    private function writeError(\Throwable $exception, ?\SplFileInfo $file = null): void
    {
        $previousException = $exception->getPrevious();
        if ($previousException) {
            $this->writeError($previousException);
        }

        $message = "[ERROR] {$exception->getMessage()} on {$exception->getFile()}:{$exception->getLine()}";
        if ($file) {
            $message .= ". Cannot process file: {$file->getPathname()}";
        }
        if ($exception instanceof CommentRegistrationException) {
            $message .= " with comment on line {$exception->getToken()->line}";
            $message .= " and obtained text: {$exception->getCommentPart()->getContent()}";
        }
        $message .= "\n";

        if (!$previousException) {
            $message .= "Stack trace:\n {$exception->getTraceAsString()} \n";
        }

        $this->output->writeErr($message);
    }
}
