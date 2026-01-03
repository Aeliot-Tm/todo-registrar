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

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[AsTaggedItem(index: RegistrarType::GitLab->value)]
final class GitlabRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = func_get_arg(1);
        $generalIssueConfig = $this->createGeneralIssueConfig($config['issue'] ?? [], $validator);
        $apiClientProvider = new ApiClientFactory($config['service']);
        $apiSectionClientFactory = new ApiSectionClientFactory(
            $apiClientProvider->createClient(),
            $this->getProjectIdentifier($config['service']),
        );
        $milestoneApiClient = $apiSectionClientFactory->createMilestoneService();

        return new GitlabRegistrar(
            $apiSectionClientFactory->createIssueService(),
            new IssueFactory(
                $generalIssueConfig,
                $apiSectionClientFactory->createUserResolver(),
                $milestoneApiClient,
            ),
            $apiSectionClientFactory->createLabelService(),
            $milestoneApiClient,
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

    /**
     * @param array<string,mixed> $config
     */
    private function getProjectIdentifier(array $config): int|string
    {
        // Either path or ID
        $projectIdentifier = $config['project'] ?? null;
        if ('' === (string) $projectIdentifier) {
            throw new \InvalidArgumentException('Project identifier must be specified in service config');
        }

        // If already an integer, return as is
        if (\is_int($projectIdentifier) || ctype_digit((string) $projectIdentifier)) {
            return (int) $projectIdentifier;
        }

        // Otherwise, it's a project path (return as string, API will URL-encode it)
        return $projectIdentifier;
    }
}
