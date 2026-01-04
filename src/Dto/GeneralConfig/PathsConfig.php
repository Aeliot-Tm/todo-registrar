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

/**
 * DTO for validating of paths configuration.
 *
 * @internal
 */
final class PathsConfig
{
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Option "paths.append" must be a string or array of strings'),
            new Assert\Sequentially(constraints: [
                new Assert\Type(type: 'array', message: 'Option "paths.append" must be a string or array of strings'),
                new Assert\All(constraints: [new Assert\Type(type: 'string', message: 'Each path in "paths.append" must be a string')]),
            ]),
        ],
        message: 'Option "paths.append" must be a string or array of strings'
    )]
    private mixed $append = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Option "paths.exclude" must be a string or array of strings'),
            new Assert\Sequentially(constraints: [
                new Assert\Type(type: 'array', message: 'Option "paths.exclude" must be a string or array of strings'),
                new Assert\All(constraints: [new Assert\Type(type: 'string', message: 'Each path in "paths.exclude" must be a string')]),
            ]),
        ],
        message: 'Option "paths.exclude" must be a string or array of strings'
    )]
    private mixed $exclude = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'string', message: 'Option "paths.in" must be a string or array of strings'),
            new Assert\Sequentially(constraints: [
                new Assert\Type(type: 'array', message: 'Option "paths.in" must be a string or array of strings'),
                new Assert\All(constraints: [new Assert\Type(type: 'string', message: 'Each path in "paths.in" must be a string')]),
            ]),
        ],
        message: 'Option "paths.in" must be a string or array of strings'
    )]
    private mixed $in = null;

    #[Assert\IsNull(message: 'Unknown "paths" options detected: {{ value }}')]
    private mixed $invalidKeys = null;

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(array $options)
    {
        $this->in = $options['in'] ?? null;
        $this->append = $options['append'] ?? null;
        $this->exclude = $options['exclude'] ?? null;

        $knownKeys = ['in', 'append', 'exclude'];
        $unknownKeys = array_diff(array_keys($options), $knownKeys);
        if ($unknownKeys) {
            $this->invalidKeys = implode(', ', $unknownKeys);
        }
    }

    /**
     * @return string|string[]|null
     */
    public function getAppend(): string|array|null
    {
        return $this->append;
    }

    /**
     * @return string|string[]|null
     */
    public function getIn(): string|array|null
    {
        return $this->in;
    }

    /**
     * @return string|string[]|null
     */
    public function getExclude(): string|array|null
    {
        return $this->exclude;
    }

    public function getInvalidKeys(): ?string
    {
        return $this->invalidKeys;
    }
}
