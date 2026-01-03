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

namespace Aeliot\TodoRegistrar\Service\Registrar\GitHub;

use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[AsTaggedItem(index: RegistrarType::GitHub->value)]
final readonly class GitHubRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config, ?ValidatorInterface $validator = null): RegistrarInterface
    {
        $validator ??= ValidatorFactory::create();
        $generalIssueConfig = $this->createGeneralConfig($config['issue'] ?? [], $validator);
        $apiClientFactory = new ApiClientFactory($config['service']);

        return new GitHubRegistrar(
            $apiClientFactory->createIssueApiClient(),
            new IssueFactory($generalIssueConfig),
            $apiClientFactory->createLabelApiClient(),
        );
    }

    /**
     * @param array<string,mixed> $issue
     */
    public function createGeneralConfig(array $issue, ValidatorInterface $validator): GeneralIssueConfig
    {
        $generalIssueConfig = new GeneralIssueConfig($issue);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[GitHub] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
