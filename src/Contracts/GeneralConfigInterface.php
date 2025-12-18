<?php
declare(strict_types=1);

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
