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

namespace Aeliot\TodoRegistrar\Console;

use Aeliot\TodoRegistrar\Exception\InvalidOptionException;

/**
 * @internal
 */
final class OptionsReader
{
    /**
     * @return array{
     *     config: string|null,
     *     quiet: bool|null,
     *     verbose: int|string|null
     * }
     */
    public function getOptions(): array
    {
        $values = [];
        /** @var array<string,string> $options */
        $options = getopt('c:qv::', ['config:', 'quiet', 'verbose::']);
        $defaults = [
            'config' => ['c', null],
            'quiet' => ['q', null],
            'verbose' => ['v', '1'],
        ];

        foreach ($defaults as $long => [$short, $default]) {
            if (isset($options[$short], $options[$long])) {
                throw new InvalidOptionException(\sprintf('Option %s is duplicated', $long));
            }
            $values[$long] = $options[$short] ?? $options[$long] ?? $default;
        }

        if (false === $values['quiet']) {
            $values['verbose'] = '-1';
        } elseif (null !== $values['quiet']) {
            throw new InvalidOptionException('Invalid value for option "quiet"');
        }

        if (false === $values['verbose']) {
            if (\array_key_exists('verbose', $options)) {
                $values['verbose'] = '0';
            } elseif (\array_key_exists('v', $options)) {
                $values['verbose'] = '1';
            }
        }

        return $values;
    }
}
