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
 * Builds context path string in code block format.
 *
 * @internal
 */
#[AsTaggedItem(index: ContextPathBuilderFormat::NUMBERED->value)]
final readonly class NumberedContextPathBuilder extends AbstractContextPathBuilder implements ContextPathBuilderInterface
{
    public function build(array $nodes): string
    {
        $number = 1;

        return implode("\n", array_map(static function (string $x) use (&$number): string {
            return ($number++) . ". $x";
        }, $this->getLines($nodes))) . "\n";
    }
}
