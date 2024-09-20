<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

final class GithubRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        $issueConfig = $config['issue'] ?? [];

        return new GithubRegistrar(
            new IssueFactory(new IssueConfig($issueConfig)),
            new ServiceFactory($config['service'])
        );
    }
}
