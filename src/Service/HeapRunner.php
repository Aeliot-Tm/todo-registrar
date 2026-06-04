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
use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use Aeliot\TodoRegistrar\Dto\ProcessStatistic;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Exception\FileReadException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGateRegistry;
use Aeliot\TodoRegistrar\Service\File\FileParserRegistry;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrarContracts\FinderInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\ProcessConfigAwareInterface;
use Aeliot\TodoRegistrarContracts\GeneralConfig\ProcessConfigInterface;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;

/**
 * @internal
 */
final readonly class HeapRunner
{
    public function __construct(
        private CommentExtractor $commentExtractor,
        private FinderInterface $finder,
        private FileParserRegistry $fileParserRegistry,
        private SequentialCommentGlueGateRegistry $glueGateRegistry,
        private OutputAdapter $output,
        private RegistrarInterface $registrar,
        private Saver $saver,
        private TodoBuilder $todoBuilder,
        private GeneralConfigInterface $config,
    ) {
    }

    /**
     * @throws CommentRegistrationException
     * @throws FileReadException
     * @throws LogicException
     * @throws NoLineException
     */
    public function run(): ProcessStatistic
    {
        $statistic = new ProcessStatistic();
        $hashToKey = [];
        $glueSameTickets = $this->getGlueSameTickets();

        foreach ($this->getTodos($statistic) as [$todo, $fileUpdateCallback]) {
            $this->register($todo, $glueSameTickets, $hashToKey, $statistic);
            $fileUpdateCallback();
        }

        return $statistic;
    }

    /**
     * @return \Generator<array{0: CommentPart, 1: callable}>
     *
     * @throws LogicException
     */
    private function getCommentParts(ProcessStatistic $statistic): \Generator
    {
        foreach ($this->getFileHeaps($statistic) as $fileHeap) {
            foreach ($fileHeap->getCommentNodes() as $commentNode) {
                $todos = $this->commentExtractor->extract($commentNode);

                foreach ($todos as $commentPart) {
                    $ticketKey = $commentPart->getTagMetadata()?->getTicketKey();
                    if ($ticketKey) {
                        $statistic->tickIgnoredTodo();
                        $this->output->writeln("Skip TODO with Key: {$ticketKey}", OutputAdapter::VERBOSITY_DEBUG);
                        continue;
                    }

                    yield [
                        $commentPart,
                        $fileHeap->getFileUpdateCallback(),
                    ];
                }
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function getExtensionAliases(): array
    {
        return array_map('strtolower', ($this->config instanceof ProcessConfigAwareInterface
            ? $this->config->getProcessConfig()?->getExtensionAliases()
            : null) ?? []);
    }

    /**
     * @return \Generator<FileHeap>
     *
     * @throws FileReadException
     * @throws LogicException
     */
    private function getFileHeaps(ProcessStatistic $statistic): \Generator
    {
        $extensionAliases = $this->getExtensionAliases();
        $glueSequentialComments = $this->getGlueSequentialComments();

        foreach ($this->finder as $file) {
            $extension = strtolower($file->getExtension());
            $extensionAlias = $extensionAliases[$extension] ?? $extension;
            $fileParser = $this->fileParserRegistry->findParser($extensionAlias);
            if (!$fileParser) {
                $this->output->writeErr("There is not configured parser for file: {$file->getPathname()}", OutputAdapter::VERBOSITY_NORMAL);
                continue;
            }

            $this->output->writeln("Begin process file: {$file->getPathname()}", OutputAdapter::VERBOSITY_DEBUG);
            try {
                $glueGate = null;
                if ($glueSequentialComments) {
                    $glueGate = $this->glueGateRegistry->find($extensionAlias);
                    if (null === $glueGate) {
                        throw new LogicException(\sprintf('Sequential comment glue is enabled but no glue gate is configured for extension alias "%s" (file: %s)', $extensionAlias, $file->getPathname()));
                    }
                }

                $fileHeap = new FileHeap(
                    $fileParser->parse($file),
                    $glueSequentialComments,
                    $glueGate,
                    $statistic,
                    $this->saver,
                );

                $countCommentNodes = \count($fileHeap->getCommentNodes());
                if ($this->output->isDebug()) {
                    $this->output->writeln("Detected comment nodes: {$countCommentNodes}");
                }

                yield $fileHeap;

                if (
                    $this->output->isDebug()
                    || ($this->output->isVeryVerbose() && $countCommentNodes)
                    || ($this->output->isVerbose() && $fileHeap->getRegistrationCount())
                ) {
                    $this->output->writeln(
                        "Registered {$fileHeap->getRegistrationCount()} for file: {$file->getPathname()}"
                    );
                }
            } catch (\Throwable $exception) {
                /*
                 * TODO: #196 refactor handling of exceptions
                 *       Consider points when continuing is possible and when is not
                 */
                $this->writeError($exception, $file);
                throw $exception;
            }
        }
    }

    private function getGlueSameTickets(): bool
    {
        $processConfig = $this->config instanceof ProcessConfigAwareInterface
            ? $this->config->getProcessConfig()
            : null;

        $isGlueSameTicket = null;
        if ($processConfig instanceof ProcessConfigInterface) {
            $isGlueSameTicket = $processConfig->isGlueSameTicket();
        }

        return $isGlueSameTicket ?? ProcessConfig::DEFAULT_GLUE_SAME_TICKETS;
    }

    private function getGlueSequentialComments(): bool
    {
        return ($this->config instanceof ProcessConfigAwareInterface
            ? $this->config->getProcessConfig()?->isGlueSequentialComments()
            : null) ?? ProcessConfig::DEFAULT_GLUE_SEQUENTIAL_COMMENTS;
    }

    /**
     * @return \Generator<array{0: Todo, 1: callable}>
     *
     * @throws LogicException
     * @throws NoLineException
     */
    private function getTodos(ProcessStatistic $statistic): \Generator
    {
        foreach ($this->getCommentParts($statistic) as [$commentPart, $fileUpdateCallback]) {
            yield [$this->todoBuilder->create($commentPart), $fileUpdateCallback];
        }
    }

    /**
     * @param array<string,string> $hashToKey
     *
     * @throws CommentRegistrationException
     */
    private function register(Todo $todo, bool $glueSameTickets, array &$hashToKey, ProcessStatistic $statistic): void
    {
        try {
            $hash = $todo->getHash();
            if ($glueSameTickets && isset($hashToKey[$hash])) {
                $key = $hashToKey[$hash];
                $this->output->writeln("Injected existing key: {$hashToKey[$hash]}", OutputAdapter::VERBOSITY_VERBOSE);
                $statistic->tickGluedTodo();
            } else {
                $hashToKey[$hash] = $key = $this->registrar->register($todo);
                $this->output->writeln("Registered new key: $key", OutputAdapter::VERBOSITY_VERBOSE);
            }
            $todo->injectKey($key);
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
            $message .= " with comment on line {$exception->getStartLine()}";
            $message .= " and obtained text: {$exception->getCommentPart()->getContent()}";
        }
        $message .= "\n";

        if (!$previousException) {
            $message .= "Stack trace:\n {$exception->getTraceAsString()} \n";
        }

        $this->output->writeErr($message);
    }
}
