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

use Aeliot\TodoRegistrar\Console\Output;
use Aeliot\TodoRegistrar\Exception\CommentRegistrationException;
use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

class CommentRegistrar
{
    public function __construct(
        private CommentDetector $commentDetector,
        private CommentExtractor $commentExtractor,
        private RegistrarInterface $registrar,
        private TodoFactory $todoFactory,
    ) {
    }

    /**
     * @param \PhpToken[] $tokens
     */
    public function register(array $tokens, Output $output): int
    {
        $commentTokens = $this->commentDetector->filter($tokens);
        if (!$commentTokens) {
            return 0;
        }

        return $this->registerTodos($commentTokens, $output);
    }

    /**
     * @param \PhpToken[] $tokens
     */
    private function registerTodos(array $tokens, Output $output): int
    {
        $countNewTodo = 0;
        foreach ($tokens as $token) {
            $commentParts = $this->commentExtractor->extract($token->text);
            foreach ($commentParts->getTodos() as $commentPart) {
                if ($commentPart->getTagMetadata()?->getTicketKey()) {
                    if ($output->isVeryVerbose()) {
                        $output->writeln("Skip TODO with Key: {$commentPart->getTagMetadata()?->getTicketKey()}");
                    }
                    continue;
                }
                $todo = $this->todoFactory->create($commentPart);
                if ($this->registrar->isRegistered($todo)) {
                    if ($output->isVeryVerbose()) {
                        $output->writeln("Detected TODO is registered: {$todo->getSummary()}");
                    }
                    continue;
                }
                try {
                    $key = $this->registrar->register($todo);
                    if ($output->isVerbose()) {
                        $output->writeln("Registered new key: $key");
                    }
                } catch (\Throwable $exception) {
                    throw new CommentRegistrationException($commentPart, $token, $exception);
                }
                $commentPart->injectKey($key);
                ++$countNewTodo;
            }

            $token->text = $commentParts->getContent();
        }

        return $countNewTodo;
    }
}
