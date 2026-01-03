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

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTaggedItem(index: RegistrarType::JIRA->value)]
final class JiraRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config): RegistrarInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = func_get_arg(1);
        $issueConfig = ($config['issue'] ?? []) + ['projectKey' => $config['projectKey']];
        $generalIssueConfig = $this->createGeneralIssueConfig($issueConfig, $validator);

        $defaultIssueLinkType = $config['issueLinkType'] ?? 'Relates';
        $serviceFactory = new ServiceFactory($config['service']);
        $issueLinkService = $serviceFactory->createIssueLinkService();

        return new JiraRegistrar(
            new IssueFieldFactory($generalIssueConfig),
            new IssueLinkRegistrar(
                new LinkedIssueNormalizer(
                    $defaultIssueLinkType,
                    new IssueLinkTypeProvider($issueLinkService)
                ),
                $issueLinkService,
            ),
            $serviceFactory->createIssueService(),
        );
    }

    /**
     * @param array<string,mixed> $issueConfig
     */
    public function createGeneralIssueConfig(array $issueConfig, ValidatorInterface $validator): GeneralIssueConfig
    {
        $generalIssueConfig = new GeneralIssueConfig($issueConfig);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[JIRA] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
