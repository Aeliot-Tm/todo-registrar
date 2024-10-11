<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit;

use Aeliot\TodoRegistrar\ArrayConfigFactory;
use Aeliot\TodoRegistrar\ConfigFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder as SymfonyFinder;

#[CoversClass(ConfigFactory::class)]
#[CoversClass(ArrayConfigFactory::class)]
final class ConfigFactoryTest extends TestCase
{
    public function testYamlConfig(): void
    {
        $config = (new ConfigFactory(new ArrayConfigFactory()))->create(__DIR__ . '/../fixtures/simple_config.yaml');

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
        self::assertSame([dirname(__DIR__, 2)], $propertyDirs->getValue($finder));

        $propertyExclude = $reflection->getProperty('exclude');
        $propertyExclude->setAccessible(true);
        self::assertSame(['vendor', 'tests/fixtures', 'var',], $propertyExclude->getValue($finder));

        $propertyIterators = $reflection->getProperty('iterators');
        $propertyIterators->setAccessible(true);
        $appends = array_keys(array_merge(...array_map(
            static fn (iterable $it): array => iterator_to_array($it),
            $propertyIterators->getValue($finder),
        )));
        self::assertSame(['bin/todo-registrar'], $appends);
    }
}
