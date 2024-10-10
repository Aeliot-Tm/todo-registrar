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

namespace Aeliot\TodoRegistrar;

/**
 * @internal
 */
final class OptionsReader
{
    public function getOptions(): array
    {
        $values = [];
        $options = getopt('c:', ['config:']);
        $defaults = [
            'config' => ['c', null],
        ];

        foreach ($defaults as $long => [$short, $default]) {
            if (isset($options[$short], $options[$long])) {
                throw new \InvalidArgumentException(\sprintf('Option %s is duplicated', $long));
            }
            $values[$long] = $options[$short] ?? $options[$long] ?? $default;
        }

        return $values;
    }
}
