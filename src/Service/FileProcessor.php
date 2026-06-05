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
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Exception\NoLineException;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;

/**
 * @internal
 */
final readonly class FileProcessor
{
    public function __construct(
        private CommentExtractor $commentExtractor,
        private RegistrarInterface $registrar,
        private TodoBuilder $todoBuilder,
    ) {
    }

    /**
     * @throws CommentRegistrationException
     * @throws NoLineException
     */
    public function process(FileHeap $fileHeap, HeapContext $context): void
    {
        foreach ($fileHeap->getCommentNodes() as $commentNode) {
            foreach ($this->commentExtractor->extract($commentNode) as $commentPart) {
                if ($this->skipRegisteredTodo($commentPart, $context)) {
                    continue;
                }

                $todo = $this->todoBuilder->create($commentPart);
                $this->register($todo, $context);
                $fileHeap->saveAfterRegistration();
            }
        }
    }

    /**
     * @throws CommentRegistrationException
     */
    private function register(Todo $todo, HeapContext $context): void
    {
        try {
            $hash = $todo->getHash();
            if ($context->glueSameTickets && isset($context->hashToKey[$hash])) {
                $key = $context->hashToKey[$hash];
                $context->output->writeln("Injected existing key: {$context->hashToKey[$hash]}", OutputAdapter::VERBOSITY_VERBOSE);
                $context->statistic->tickGluedTodo();
            } else {
                $context->hashToKey[$hash] = $key = $this->registrar->register($todo);
                $context->output->writeln("Registered new key: $key", OutputAdapter::VERBOSITY_VERBOSE);
            }
            $todo->injectKey($key);
        } catch (\Throwable $exception) {
            throw new CommentRegistrationException($todo, $exception);
        }
    }

    private function skipRegisteredTodo(CommentPart $commentPart, HeapContext $context): bool
    {
        $ticketKey = $commentPart->getTagMetadata()?->getTicketKey();
        if (!$ticketKey) {
            return false;
        }

        $context->statistic->tickIgnoredTodo();
        $context->output->writeln("Skip TODO with Key: {$ticketKey}", OutputAdapter::VERBOSITY_DEBUG);

        return true;
    }
}
