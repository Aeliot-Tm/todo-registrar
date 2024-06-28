<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Exception;

final class CollectionDuplicatedKeyException extends \DomainException
{
    public function __construct(string $key)
    {
        parent::__construct(sprintf('Key "%s" is duplicated', $key));
    }
}