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
use Aeliot\TodoRegistrar\Contracts\FinderInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;

final readonly class HeapRunner
{
    public function __construct(
        private CommentDetector $commentDetector,
        private CommentExtractor $commentExtractor,
        private FinderInterface $finder,
        private OutputAdapter $output,
        private RegistrarInterface $registrar,
        private Saver $saver,
        private TodoBuilder $todoBuilder,
        private Tokenizer $tokenizer,
    ) {
    }

    public function run(): ProcessStatistic
    {
        $statistic = new ProcessStatistic();
        foreach ($this->getTodos($statistic) as [$todo, $fileUpdateCallback]) {
            $this->register($todo);
            $fileUpdateCallback();
        }

        return $statistic;
    }

    /**
     * @return \Generator<array{0: CommentPart, 2: callable}>
     */
    private function getCommentParts(ProcessStatistic $statistic): \Generator
    {
        foreach ($this->getFileHeaps($statistic) as $fileHeap) {
            foreach ($fileHeap->getCommentTokens() as $token) {
                // TODO: #13 implement gluing of simple comments
                $commentParts = $this->commentExtractor->extract($token->text, $token);
                foreach ($commentParts->getTodos() as $commentPart) {
                    $ticketKey = $commentPart->getTagMetadata()?->getTicketKey();
                    if ($ticketKey) {
                        $this->output->writeln("Skip TODO with Key: {$ticketKey}", OutputAdapter::VERBOSITY_DEBUG);
                        continue;
                    }

                    yield [$commentPart, $fileHeap->getFileUpdateCallback()];
                }
            }
        }
    }

    /**
     * @return \Generator<FileHeap>
     */
    private function getFileHeaps(ProcessStatistic $statistic): \Generator
    {
        foreach ($this->finder as $file) {
            $this->output->writeln("Begin process file: {$file->getPathname()}", OutputAdapter::VERBOSITY_DEBUG);
            try {
                $tokens = $this->tokenizer->tokenize($file);
                $commentTokens = $this->commentDetector->filter($tokens);
                $countCommentTokens = \count($commentTokens);

                if ($this->output->isDebug()) {
                    $this->output->writeln("Detected comment tokens: {$countCommentTokens}");
                }

                $fileHeap = new FileHeap(
                    $commentTokens,
                    $tokens,
                    $file,
                    $statistic,
                    $this->saver,
                );
                yield $fileHeap;

                if (
                    $this->output->isDebug()
                    || ($this->output->isVeryVerbose() && $countCommentTokens)
                    || ($this->output->isVerbose() && $fileHeap->getRegistrationCounter())
                ) {
                    $this->output->writeln(
                        "Registered {$fileHeap->getRegistrationCounter()} for file: {$file->getPathname()}"
                    );
                }
            } catch (\Throwable $exception) {
                /*
                 * TODO: refactor handling of exceptions
                 *       Consider points when continuing is possible and when is not
                 */
                $this->writeError($exception, $file);
                throw $exception;
            }
        }
    }

    /**
     * @return \Generator<array{0: Todo, 2: callable}>
     */
    private function getTodos(ProcessStatistic $statistic): \Generator
    {
        foreach ($this->getCommentParts($statistic) as [$commentPart, $fileUpdateCallback]) {
            yield [$this->todoBuilder->create($commentPart), $fileUpdateCallback];
        }
    }

    private function register(Todo $todo): void
    {
        try {
            $key = $this->registrar->register($todo);
            $todo->injectKey($key);
            $this->output->writeln("Registered new key: $key", OutputAdapter::VERBOSITY_VERBOSE);
        } catch (\Throwable $exception) {
            throw new CommentRegistrationException($todo, $exception);
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

        $this->output->writeErr($message);
    }
}
