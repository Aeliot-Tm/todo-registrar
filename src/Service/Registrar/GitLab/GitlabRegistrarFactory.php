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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitLab;

use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * TODO: #199 make assertion of GitLab config with symfony/validator component.
 */
#[AsTaggedItem(index: RegistrarType::GitLab->value)]
final class GitlabRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        $issueConfig = $config['issue'] ?? [];
        $apiClientProvider = new ApiClientProvider($config['service']);

        return new GitlabRegistrar(
            new IssueFactory(
                new IssueConfig($issueConfig),
                $apiClientProvider->getUserResolver(),
                $apiClientProvider->getMilestoneService(),
            ),
            $apiClientProvider,
        );
    }
}
