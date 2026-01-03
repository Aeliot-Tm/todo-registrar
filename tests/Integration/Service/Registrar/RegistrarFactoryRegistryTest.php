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

namespace Aeliot\TodoRegistrar\Test\Integration\Service\Registrar;

use Aeliot\TodoRegistrar\Console\ContainerBuilder;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\Registrar\GitHub\GitHubRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\GitLab\GitlabRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\JiraRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\RedmineRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;
use Aeliot\TodoRegistrar\Service\Registrar\YandexTracker\YandexTrackerRegistrarFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistrarFactoryRegistry::class)]
final class RegistrarFactoryRegistryTest extends TestCase
{
    private RegistrarFactoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = ContainerBuilder::build()->get(RegistrarFactoryRegistry::class);
    }

    /**
     * @return iterable<array{0: RegistrarType, 1: class-string}>
     */
    public static function getImplementedRegistrarTypes(): iterable
    {
        yield [GitHubRegistrarFactory::class, RegistrarType::GitHub];
        yield [GitlabRegistrarFactory::class, RegistrarType::GitLab];
        yield [JiraRegistrarFactory::class, RegistrarType::JIRA];
        yield [RedmineRegistrarFactory::class, RegistrarType::Redmine];
        yield [YandexTrackerRegistrarFactory::class, RegistrarType::YandexTracker];
    }

    /**
     * @return iterable<array{0: RegistrarType}>
     */
    public static function getNotImplementedRegistrarTypes(): iterable
    {
        yield [RegistrarType::AzureBoards];
        yield [RegistrarType::YouTrack];
    }

    #[DataProvider('getImplementedRegistrarTypes')]
    public function testImplementedRegistrarTypes(string $expectedFactoryClass, RegistrarType $type): void
    {
        self::assertInstanceOf($expectedFactoryClass, $this->registry->getFactory($type));
    }

    #[DataProvider('getNotImplementedRegistrarTypes')]
    public function testNotImplementedRegistrarTypes(RegistrarType $type): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(\sprintf('Not supported registrar type "%s"', $type->value));

        $this->registry->getFactory($type);
    }
}
