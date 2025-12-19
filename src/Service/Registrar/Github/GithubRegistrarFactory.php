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

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * TODO: #146 make assertion of Github config with symfony/validator component.
 */
#[AsTaggedItem(index: RegistrarType::Github->value)]
final class GithubRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        $issueConfig = $config['issue'] ?? [];

        return new GithubRegistrar(
            new IssueFactory(new IssueConfig($issueConfig)),
            new ApiClientFactory($config['service'])
        );
    }
}
