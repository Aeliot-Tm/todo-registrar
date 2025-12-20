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

namespace Aeliot\TodoRegistrar\Service\Config;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for validating of registrar configuration.
 *
 * @internal
 */
final class RegistrarConfig
{
    #[Assert\NotBlank(message: 'Option "registrar.type" is required')]
    #[Assert\Type(type: 'string', message: 'Option "registrar.type" must be a string')]
    private mixed $type = null;

    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\IsNull(),
            new Assert\Type(type: 'array', message: 'Option "registrar.options" must be an array'),
        ],
        message: 'Option "registrar.options" must be an array or null'
    )]
    private mixed $options = null;

    #[Assert\IsNull(message: 'Unknown "registrar" options detected: {{ value }}')]
    private mixed $invalidKeys = null;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->type = $config['type'] ?? null;
        $this->options = $config['options'] ?? null;

        $knownKeys = ['type', 'options'];
        $unknownKeys = array_diff(array_keys($config), $knownKeys);
        if ($unknownKeys) {
            $this->invalidKeys = implode(', ', $unknownKeys);
        }
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function getInvalidKeys(): ?string
    {
        return $this->invalidKeys;
    }
}
