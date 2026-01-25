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

namespace Aeliot\TodoRegistrar\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

/**
 * @internal
 */
final class Application extends SymfonyApplication
{
    // TODO: #197 automate updating of application version
    private const VERSION = '3.1.0';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('TODO Registrar', self::VERSION);

        $commandMap = [];
        if ($container instanceof TaggedContainerInterface) {
            foreach ($container->findTaggedServiceIds('console.command') as $serviceId => $tags) {
                try {
                    $command = $container->get($serviceId);
                    if ($command instanceof Command) {
                        $name = $command->getName();
                        if ($name) {
                            $commandMap[$name] = $serviceId;
                        }
                    }
                } catch (\Throwable $e) {
                    // Skip commands that cannot be instantiated
                    continue;
                }
            }
        }

        if (!empty($commandMap)) {
            $this->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
        }
    }
}
