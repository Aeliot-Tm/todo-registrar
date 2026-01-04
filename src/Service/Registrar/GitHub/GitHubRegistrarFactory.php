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

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\ColorGenerator;
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[AsTaggedItem(index: RegistrarType::GitHub->value)]
final readonly class GitHubRegistrarFactory implements RegistrarFactoryInterface
{
    public function __construct(
        private ColorGenerator $colorGenerator,
        private IssueSupporter $issueSupporter,
    ) {
    }

    public function create(array $config): RegistrarInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = func_get_arg(1);
        $generalIssueConfig = $this->createGeneralConfig($config, $validator);
        $apiClientFactory = new ApiClientFactory($config['service'], $this->colorGenerator);

        return new GitHubRegistrar(
            $apiClientFactory->createIssueApiClient(),
            new IssueFactory($generalIssueConfig, $this->issueSupporter),
            $apiClientFactory->createLabelApiClient(),
        );
    }

    /**
     * @param array<string,mixed> $config
     */
    private function createGeneralConfig(array $config, ValidatorInterface $validator): GeneralIssueConfig
    {
        // Get owner and repository from issue config, fallback to service config
        $serviceConfig = $config['service'] ?? [];
        $issueConfig = ($config['issue'] ?? []) + [
            'owner' => $serviceConfig['owner'] ?? null,
            'repository' => $serviceConfig['repository'] ?? null,
        ];
        // Normalize composite repository format from service config if needed
        // Only parse if repository is not already set in issue config
        if (!isset($issueConfig['owner'])
            && isset($issueConfig['repository'])
            && \is_string($issueConfig['repository'])
            && str_contains($issueConfig['repository'], '/')
        ) {
            [$owner, $repository] = explode('/', $issueConfig['repository'], 2);
            $issueConfig['owner'] = $owner;
            $issueConfig['repository'] = $repository;
        }

        $generalIssueConfig = new GeneralIssueConfig($issueConfig);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[GitHub] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
