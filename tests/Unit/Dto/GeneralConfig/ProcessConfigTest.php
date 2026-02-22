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

namespace Aeliot\TodoRegistrar\Test\Unit\Dto\GeneralConfig;

use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessConfig::class)]
final class ProcessConfigTest extends TestCase
{
    public function testDefaultValue(): void
    {
        $config = new ProcessConfig();
        self::assertFalse($config->isGlueSequentialComments());
    }

    public function testGetterSetter(): void
    {
        $config = new ProcessConfig();
        $config->setGlueSequentialComments(true);
        self::assertTrue($config->isGlueSequentialComments());

        $config->setGlueSequentialComments(false);
        self::assertFalse($config->isGlueSequentialComments());
    }
}
