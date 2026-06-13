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

use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Dto\GeneralConfig\ProcessConfig;
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\HeapContextFactory;
use Aeliot\TodoRegistrar\Test\Stub\IncrementalRegistrarFactory;
use Aeliot\TodoRegistrarContracts\GeneralConfig\GeneralConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(HeapContextFactory::class)]
#[UsesClass(HeapContext::class)]
#[UsesClass(ProcessConfig::class)]
final class HeapContextFactoryTest extends TestCase
{
    public function testCreatesDefaultsWhenProcessConfigIsUnavailable(): void
    {
        $config = $this->createMock(GeneralConfigInterface::class);
        $output = new OutputAdapter(new NullOutput());

        $context = (new HeapContextFactory())->create($config, $output);

        self::assertSame($output, $context->output);
        self::assertSame([], $context->extensionAliases);
        self::assertSame([], $context->hashToKey);
        self::assertFalse($context->glueSameTickets);
        self::assertFalse($context->glueSequentialComments);
        self::assertSame(0, $context->statistic->getCountAnalyzedFiles());
    }

    public function testMapsProcessConfigOptions(): void
    {
        $processConfig = new ProcessConfig();
        $processConfig->setGlueSameTickets(true);
        $processConfig->setGlueSequentialComments(true);
        $processConfig->setExtensionAliases(['module' => 'php', 'yaml' => 'yaml']);

        $config = (new Config())
            ->setFinder((new Finder())->in(sys_get_temp_dir())->name('*.none')->sortByName(true))
            ->setRegistrar(IncrementalRegistrarFactory::class, ['prefix' => 'KEY'])
            ->setProcessConfig($processConfig);

        $context = (new HeapContextFactory())->create($config, new OutputAdapter(new NullOutput()));

        self::assertTrue($context->glueSameTickets);
        self::assertTrue($context->glueSequentialComments);
        self::assertSame(['module' => 'php', 'yaml' => 'yaml'], $context->extensionAliases);
    }
}
