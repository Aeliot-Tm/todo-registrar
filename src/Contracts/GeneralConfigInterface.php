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

namespace Aeliot\TodoRegistrar\Contracts;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface as GeneralConfigContractInterface;

/**
 * @deprecated use {@see GeneralConfigContractInterface }. Note support of {@see RegistrarType }
 *             will be removed from method {@see GeneralConfigContractInterface::getRegistrarType() }
 *             to make it simple and flexible.
 */
interface GeneralConfigInterface
{
    public function getFinder(): FinderInterface;

    public function getInlineConfigFactory(): ?InlineConfigFactoryInterface;

    public function getInlineConfigReader(): ?InlineConfigReaderInterface;

    /**
     * @return array<string,mixed>
     */
    public function getRegistrarConfig(): array;

    public function getRegistrarType(): RegistrarType|RegistrarFactoryInterface|string;

    /**
     * @return string[]
     */
    public function getTags(): array;
}

trigger_deprecation(
    'aeliot/todo-registrar',
    '2.3.0',
    'Use interfaces from "aeliot/todo-registrar-contracts". '
    . 'Interface %s will be removed in version 3.0.0 and method "getRegistrarType" will not support %s.',
    GeneralConfigInterface::class,
    RegistrarType::class,
);
