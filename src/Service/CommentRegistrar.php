<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

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
    public function register(array $tokens): bool
    {
        $commentTokens = $this->commentDetector->filter($tokens);
        if (!$commentTokens) {
            return false;
        }

        return $this->registerTodos($commentTokens);
    }

    /**
     * @param \PhpToken[] $tokens
     */
    private function registerTodos(array $tokens): bool
    {
        $hasNewTodo = false;
        foreach ($tokens as $token) {
            $commentParts = $this->commentExtractor->extract($token->text);
            foreach ($commentParts->getTodos() as $commentPart) {
                $todo = $this->todoFactory->create($commentPart);
                if ($this->registrar->isRegistered($todo)) {
                    continue;
                }
                $key = $this->registrar->register($todo);
                $commentPart->injectKey($key);
                $hasNewTodo = true;
            }

            $token->text = $commentParts->getContent();
        }

        return $hasNewTodo;
    }
}