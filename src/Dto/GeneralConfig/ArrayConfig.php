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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO for validating array configuration before creating Config object.
 *
 * @internal
 */
#[Assert\Callback('validateNestedConfigs')]
final class ArrayConfig
{
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: PathsConfig::class, message: 'Option "paths" must be an array'),
        ],
        message: 'Option "paths" must be an object or not passed at all'
    )]
    private mixed $paths;

    #[Assert\Sequentially(constraints: [
        new Assert\NotNull(message: 'Option "registrar" is required'),
        new Assert\Type(type: RegistrarConfig::class, message: 'Option "registrar" must be an array'),
    ])]
    private mixed $registrar;

    /**
     * @var string[]|mixed
     */
    #[Assert\Sequentially(constraints: [
        new Assert\Type(type: 'array', message: 'Option "tags" must be an array of strings'),
        new Assert\All(constraints: [new Assert\Type(type: 'string', message: 'Each tag must be a string')]),
    ])]
    private mixed $tags;

    #[Assert\Choice(
        callback: [IssueKeyPosition::class, 'getValues'],
        message: 'Option "issueKeyPosition" must be one of: {{ choices }}'
    )]
    private mixed $issueKeyPosition;

    #[Assert\Sequentially(constraints: [
        new Assert\Type(type: 'array', message: 'Option "summarySeparator" must be an array'),
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

    #[Assert\Sequentially(constraints: [
        new Assert\Type(type: 'string', message: 'Option "newSeparator" must be a string'),
        new Assert\Length(exactly: 1, exactMessage: 'Option "newSeparator" must be exactly 1 character'),
    ])]
    private mixed $newSeparator;

    #[Assert\NotNull(message: 'Option "replaceSeparator" cannot be null')]
    #[Assert\Type(type: 'bool', message: 'Option "replaceSeparator" must be a boolean')]
    private mixed $replaceSeparator;

    #[Assert\IsNull(message: 'Unknown configuration options detected: {{ value }}')]
    private mixed $invalidKeys = null;

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(array $options)
    {
        $paths = $options['paths'] ?? null;
        $this->paths = \is_array($paths) ? new PathsConfig($paths) : $paths;

        $registrar = $options['registrar'] ?? null;
        $this->registrar = \is_array($registrar) ? new RegistrarConfig($registrar) : $registrar;

        $this->issueKeyPosition = $options['issueKeyPosition'] ?? null;
        $this->newSeparator = $options['newSeparator'] ?? null;
        $this->replaceSeparator = $options['replaceSeparator'] ?? false;
        $this->summarySeparators = (array) ($options['summarySeparator'] ?? [':', '-']);

        $this->tags = $options['tags'] ?? [];

        $knownKeys = [
            'issueKeyPosition',
            'newSeparator',
            'paths',
            'registrar',
            'replaceSeparator',
            'summarySeparator',
            'tags',
        ];
        $unknownKeys = array_diff(array_keys($options), $knownKeys);
        if ($unknownKeys) {
            $this->invalidKeys = implode(', ', $unknownKeys);
        }
    }

    public function getPaths(): ?PathsConfig
    {
        return $this->paths;
    }

    public function getRegistrar(): RegistrarConfig
    {
        return $this->registrar;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getIssueKeyPosition(): ?string
    {
        return $this->issueKeyPosition;
    }

    public function getNewSeparator(): ?string
    {
        return $this->newSeparator;
    }

    public function getReplaceSeparator(): bool
    {
        return $this->replaceSeparator;
    }

    /**
     * @return string[]|null
     */
    public function getSummarySeparators(): ?array
    {
        return $this->summarySeparators;
    }

    public function getInvalidKeys(): ?string
    {
        return $this->invalidKeys;
    }

    public function validateNestedConfigs(ExecutionContextInterface $context): void
    {
        if ($this->paths instanceof PathsConfig) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('paths')
                ->validate($this->paths);
        }

        if ($this->registrar instanceof RegistrarConfig) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('registrar')
                ->validate($this->registrar);
        }
    }
}
