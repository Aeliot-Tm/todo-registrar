<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto\Parsing;

use Aeliot\TodoRegistrarContracts\ContextNodeInterface;

interface ContextInterface
{
    /**
     * @return ContextNodeInterface[]
     */
    public function getContextNodes(): array;
}
