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

namespace Aeliot\TodoRegistrar\Enum;

enum ContextPathBuilderFormat: string
{
    case ARROW_CHAINED = 'arrow_chained';
    case ASTERISK = 'asterisk';
    case CODE_BLOCK = 'code_block';
    case NUMBER_SIGN = 'number_sign';
    case NUMBERED = 'numbered';

    /**
     * @return string[]
     */
    public static function getValues(): array
    {
        return array_map(static fn (self $enum): string => $enum->value, self::cases());
    }
}
