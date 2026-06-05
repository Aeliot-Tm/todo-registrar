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

namespace Aeliot\TodoRegistrar\Dto;

final class HeapContext
{
    /**
     * @var array<string,string>
     */
    public array $extensionAliases;

    public bool $glueSameTickets;
    public bool $glueSequentialComments;

    /**
     * @var array<string,string>
     */
    public array $hashToKey;

    public ProcessStatistic $statistic;
}
