<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\FileProcessor;

class Application
{
    public function __construct(
        private Finder $finder,
        private FileProcessor $fileProcessor,
    ) {
    }

    public function run(): void
    {
        foreach ($this->finder as $file) {
            try {
                $this->fileProcessor->process($file);
            } catch (\Throwable $exception) {
                $this->writeError($exception, $file);
            }
        }
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

        fwrite(\STDERR, $message);
    }
}
