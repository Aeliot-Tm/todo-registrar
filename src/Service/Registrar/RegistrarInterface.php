<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Dto\Comment\CommentPart;

interface RegistrarInterface
{
    public function isRegistered(CommentPart $commentPart): bool;

    public function register(CommentPart $commentPart): void;
}