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

namespace Aeliot\TodoRegistrar\Dto\GeneralConfig;

use Aeliot\TodoRegistrar\Enum\IssueKeyPosition;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
final class IssueKeyInjectionArrayConfig
{
    #[Assert\Length(exactly: 1, exactMessage: 'Option "issueKeyInjection.newSeparator" must be exactly 1 character')]
    private mixed $newSeparator;

    #[Assert\Choice(
        callback: [IssueKeyPosition::class, 'getValues'],
        message: 'Option "issueKeyInjection.position" must be one of: {{ choices }}'
    )]
    private mixed $position;

    #[Assert\Type(type: 'bool', message: 'Option "issueKeyInjection.replaceSeparator" must be a boolean')]
    private mixed $replaceSeparator;

    /**
     * @var string[]|mixed
     */
    #[Assert\Sequentially(constraints: [
        new Assert\Type(type: 'array', message: 'Option "issueKeyInjection.summarySeparators" must be an array'),
        new Assert\All(constraints: [
            new Assert\Sequentially(constraints: [
                new Assert\Type(type: 'string', message: 'Each separator must be a string'),
                new Assert\Length(
                    exactly: 1,
                    exactMessage: 'Each separator must be exactly 1 character'
                ),
            ]),
        ]),
    ])]
    private mixed $summarySeparators;

    #[Assert\IsNull(message: 'Unknown "issueKeyInjection" options detected: {{ value }}')]
    protected ?string $invalidKeys = null;

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(array $options)
    {
        $this->position = $options['position'] ?? null;
        $this->newSeparator = $options['newSeparator'] ?? null;
        $this->replaceSeparator = $options['replaceSeparator'] ?? false;
        $this->summarySeparators = $options['summarySeparators'] ?? null;

        $knownKeys = ['issueKeyPosition', 'newSeparator', 'position', 'replaceSeparator', 'summarySeparators'];
        $unknownKeys = array_diff(array_keys($options), $knownKeys);
        if ($unknownKeys) {
            $this->invalidKeys = implode(', ', $unknownKeys);
        }
    }

    public function getNewSeparator(): ?string
    {
        return $this->newSeparator;
    }

    public function getReplaceSeparator(): bool
    {
        return (bool) $this->replaceSeparator;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @return string[]|null
     */
    public function getSummarySeparators(): ?array
    {
        return $this->summarySeparators;
    }
}
