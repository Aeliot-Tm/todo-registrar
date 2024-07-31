<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Exception;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;

final class CommentRegistrationException extends \RuntimeException
{
    public function __construct(
        private CommentPart $commentPart,
        private \PhpToken $token,
        \Throwable $previous,
    ) {
        parent::__construct(sprintf('Cannot register %s-comment', $this->commentPart->getTag()), 0, $previous);
    }

    public function getCommentPart(): CommentPart
    {
        return $this->commentPart;
    }

    public function getToken(): \PhpToken
    {
        return $this->token;
    }
}
