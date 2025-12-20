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

namespace Aeliot\TodoRegistrar\Test\Unit;

use Aeliot\TodoRegistrar\Service\Config\ArrayConfigFactory;
use Aeliot\TodoRegistrar\Service\Config\ConfigFactory;
use Aeliot\TodoRegistrar\Service\Config\YamlParser;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder as SymfonyFinder;

#[CoversClass(ConfigFactory::class)]
#[CoversClass(ArrayConfigFactory::class)]
#[CoversClass(YamlParser::class)]
final class ConfigFactoryTest extends TestCase
{
    public function testYamlConfig(): void
    {
        $validator = ValidatorFactory::create();
        $config = (new ConfigFactory(new ArrayConfigFactory($validator), new YamlParser()))->create(__DIR__ . '/../fixtures/config/simple_config.yaml');

        self::assertSame('App\RegistrarFactory', $config->getRegistrarType());
        self::assertSame([
            'issue' => [
                'labels' => 'tech-debt',
            ],
            'service' => [
                'personalAccessToken' => 'a-token',
                'owner' => 'am-i',
                'repository' => 'am-i/a-repo',
            ],
        ], $config->getRegistrarConfig());
        self::assertSame(['my_tag'], $config->getTags());

        $finder = $config->getFinder();
        $reflection = new \ReflectionClass(SymfonyFinder::class);

        $propertyDirs = $reflection->getProperty('dirs');
        $propertyDirs->setAccessible(true);
        self::assertSame([\dirname(__DIR__, 2)], $propertyDirs->getValue($finder));

        $propertyExclude = $reflection->getProperty('exclude');
        $propertyExclude->setAccessible(true);
        self::assertSame(['vendor', 'tests/fixtures', 'var'], $propertyExclude->getValue($finder));

        $propertyIterators = $reflection->getProperty('iterators');
        $propertyIterators->setAccessible(true);
        $appends = array_keys(array_merge(...array_map(
            static fn (iterable $it): array => iterator_to_array($it),
            $propertyIterators->getValue($finder),
        )));
        self::assertSame(['bin/todo-registrar'], $appends);
    }
}
