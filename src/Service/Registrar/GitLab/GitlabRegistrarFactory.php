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
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTaggedItem(index: RegistrarType::GitLab->value)]
final class GitlabRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config, ?ValidatorInterface $validator = null): RegistrarInterface
    {
        $validator ??= ValidatorFactory::create();
        $generalIssueConfig = $this->createGeneralIssueConfig($config['issue'] ?? [], $validator);
        $apiClientProvider = new ApiClientProvider($config['service']);

        return new GitlabRegistrar(
            new IssueFactory(
                $generalIssueConfig,
                $apiClientProvider->getUserResolver(),
                $apiClientProvider->getMilestoneService(),
            ),
            $apiClientProvider,
        );
    }

    /**
     * @param array<string,mixed> $issue
     */
    public function createGeneralIssueConfig(array $issue, ValidatorInterface $validator): GeneralIssueConfig
    {
        $generalIssueConfig = new GeneralIssueConfig($issue);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[GitLab] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
