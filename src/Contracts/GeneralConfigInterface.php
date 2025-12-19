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
