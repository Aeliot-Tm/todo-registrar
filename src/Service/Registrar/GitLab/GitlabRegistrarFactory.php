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
use Aeliot\TodoRegistrar\Service\Registrar\IssueSupporter;
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
    public function __construct(private IssueSupporter $issueSupporter)
    {
    }

    public function create(array $config): RegistrarInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = func_get_arg(1);
        $generalIssueConfig = $this->createGeneralIssueConfig($config, $validator);
        $apiClientProvider = new ApiClientFactory($config['service']);
        $apiSectionClientFactory = new ApiSectionClientFactory(
            $apiClientProvider->createClient(),
        );
        $milestoneApiClient = $apiSectionClientFactory->createMilestoneService();

        return new GitlabRegistrar(
            $apiSectionClientFactory->createIssueService(),
            new IssueFactory(
                $generalIssueConfig,
                $this->issueSupporter,
                $milestoneApiClient,
                $apiSectionClientFactory->createUserResolver(),
            ),
            $apiSectionClientFactory->createLabelService(),
            $milestoneApiClient,
        );
    }

    /**
     * @param array<string,mixed> $config
     */
    public function createGeneralIssueConfig(array $config, ValidatorInterface $validator): GeneralIssueConfig
    {
        $issueConfig = ($config['issue'] ?? []) + [
            'project' => ($config['service'] ?? [])['project'] ?? $config['project'] ?? null,
        ];
        if (isset($issueConfig['project']) && ctype_digit((string) $issueConfig['project'])) {
            $issueConfig['project'] = (int) $issueConfig['project'];
        }

        $generalIssueConfig = new GeneralIssueConfig($issueConfig);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[GitLab] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
