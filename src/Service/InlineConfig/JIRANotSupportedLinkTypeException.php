<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

final class JIRANotSupportedLinkTypeException extends \DomainException
{
    public function __construct(string $alias)
    {
        parent::__construct(\sprintf('"%s" is not supported type of issue link for JIRA', $alias));
    }
}
