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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar;

use Aeliot\TodoRegistrar\Service\Registrar\DryRunRegistrar;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DryRunRegistrar::class)]
final class DryRunRegistrarTest extends TestCase
{
    public function testRegisterReturnsIncrementingKeys(): void
    {
        $registrar = new DryRunRegistrar();
        $todo = $this->createMock(TodoInterface::class);

        self::assertSame('#dry-run-1', $registrar->register($todo));
        self::assertSame('#dry-run-2', $registrar->register($todo));
        self::assertSame('#dry-run-3', $registrar->register($todo));
    }
}
