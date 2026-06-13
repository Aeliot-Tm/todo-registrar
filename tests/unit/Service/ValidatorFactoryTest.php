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

namespace Aeliot\TodoRegistrar\Test\Unit\Service;

use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(ValidatorFactory::class)]
final class ValidatorFactoryTest extends TestCase
{
    public function testCreateReturnsValidator(): void
    {
        $validator = ValidatorFactory::create();

        self::assertInstanceOf(ValidatorInterface::class, $validator);
    }

    public function testCreateReturnsNewInstanceEachTime(): void
    {
        $validator1 = ValidatorFactory::create();
        $validator2 = ValidatorFactory::create();

        self::assertNotSame($validator1, $validator2);
    }
}
