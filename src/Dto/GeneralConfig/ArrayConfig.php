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

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @internal
 */
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

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: IssueKeyInjectionArrayConfig::class, message: 'Option "issueKeyInjection" must be an array'),
        ],
        message: 'Option "issueKeyInjection" must be an object or not passed at all'
    )]
    private mixed $issueKeyInjection;

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

        $issueKeyInjection = $options['issueKeyInjection'] ?? null;
        $this->issueKeyInjection = \is_array($issueKeyInjection) ? new IssueKeyInjectionArrayConfig($issueKeyInjection) : $issueKeyInjection;

        $this->tags = $options['tags'] ?? [];

        $knownKeys = [
            'issueKeyInjection',
            'paths',
            'registrar',
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

    public function getIssueKeyInjection(): ?IssueKeyInjectionArrayConfig
    {
        return $this->issueKeyInjection;
    }

    public function getInvalidKeys(): ?string
    {
        return $this->invalidKeys;
    }

    #[Assert\Callback]
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

        if ($this->issueKeyInjection instanceof IssueKeyInjectionArrayConfig) {
            $context->getValidator()
                ->inContext($context)
                ->atPath('issueKeyInjection')
                ->validate($this->issueKeyInjection);
        }
    }
}
