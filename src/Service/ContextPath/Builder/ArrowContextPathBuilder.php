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

namespace Aeliot\TodoRegistrar\Service\ContextPath\Builder;

use Aeliot\TodoRegistrar\Enum\ContextPathBuilderFormat;
use Aeliot\TodoRegistrar\Service\ContextPath\ContextPathBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @internal
 */
#[AsTaggedItem(index: ContextPathBuilderFormat::ARROW_CHAINED->value)]
final readonly class ArrowContextPathBuilder extends AbstractContextPathBuilder implements ContextPathBuilderInterface
{
    public function build(array $nodes): string
    {
        return implode(' -> ', $this->getLines($nodes)) . "\n";
    }
}
