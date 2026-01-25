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
#[AsTaggedItem(index: ContextPathBuilderFormat::ASTERISK->value)]
final readonly class AsteriskContextPathBuilder extends AbstractContextPathBuilder implements ContextPathBuilderInterface
{
    public function build(array $nodes): string
    {
        return implode("\n", array_map(static fn (string $x): string => "* $x", $this->getLines($nodes))) . "\n";
    }
}
