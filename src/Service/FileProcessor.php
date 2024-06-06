<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Service\Comment\Detector as CommentDetector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor as CommentExtractor;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

final class FileProcessor
{
    public function __construct(
        private CommentDetector $commentDetector,
        private CommentExtractor $commentExtractor,
        private RegistrarInterface $registrar,
        private Tokenizer $tokenizer,
    ) {
    }

    public function process(\SplFileInfo $file): void
    {
        $tokens = $this->tokenizer->tokenize($file);
        $commentTokens = $this->commentDetector->filter($tokens);
        if (!$commentTokens) {
            return;
        }

        if (!$this->registerTodos($commentTokens)) {
            return;
        }

        $this->saveTokensToFile($file, $tokens);
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
                if ($this->registrar->isRegistered($commentPart)) {
                    continue;
                }
                $this->registrar->register($commentPart);
                $hasNewTodo = true;
            }

            $token->text = $commentParts->getContent();
        }

        return $hasNewTodo;
    }

    /**
     * @param \PhpToken[] $tokens
     */
    private function saveTokensToFile(\SplFileInfo $file, array $tokens): void
    {
        $content = implode('', array_map(static fn(\PhpToken $x): string => $x->text, $tokens));
        file_put_contents($file->getPathname(), $content);
    }
}