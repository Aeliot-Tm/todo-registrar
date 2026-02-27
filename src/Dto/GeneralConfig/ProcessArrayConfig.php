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
 * @internal
 */
final class ProcessArrayConfig
{
    #[Assert\Type(type: 'bool', message: 'Option "process.glueSameTickets" must be a boolean')]
    private mixed $glueSameTickets;

    #[Assert\Type(type: 'bool', message: 'Option "process.glueSequentialComments" must be a boolean')]
    private mixed $glueSequentialComments;

    #[Assert\IsNull(message: 'Unknown "process" options detected: {{ value }}')]
    protected ?string $invalidKeys = null;

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(array $options)
    {
        $this->glueSameTickets = $options['glueSameTickets'] ?? false;
        $this->glueSequentialComments = $options['glueSequentialComments'] ?? false;

        $knownKeys = ['glueSameTickets', 'glueSequentialComments'];
        $unknownKeys = array_diff(array_keys($options), $knownKeys);
        if ($unknownKeys) {
            $this->invalidKeys = implode(', ', $unknownKeys);
        }
    }

    public function isGlueSameTickets(): bool
    {
        return (bool) $this->glueSameTickets;
    }

    public function isGlueSequentialComments(): bool
    {
        return (bool) $this->glueSequentialComments;
    }
}
