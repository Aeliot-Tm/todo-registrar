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

namespace Aeliot\TodoRegistrar\Exception;

use Aeliot\TodoRegistrarContracts\Exception\RegistrarException;

final class CollectionDuplicatedKeyException extends \DomainException implements RegistrarException
{
    public function __construct(string $key)
    {
        parent::__construct(\sprintf('Key "%s" is duplicated', $key));
    }
}
