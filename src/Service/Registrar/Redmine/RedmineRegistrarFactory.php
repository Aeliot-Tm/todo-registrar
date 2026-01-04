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

namespace Aeliot\TodoRegistrar\Service\Registrar\Redmine;

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
#[AsTaggedItem(index: RegistrarType::Redmine->value)]
final class RedmineRegistrarFactory implements RegistrarFactoryInterface
{
    public function __construct(private IssueSupporter $issueSupporter)
    {
    }

    public function create(array $config): RegistrarInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = func_get_arg(1);
        $issueConfig = ($config['issue'] ?? []) + ['project' => $config['project'] ?? null];
        $generalIssueConfig = $this->createGeneralIssueConfig($issueConfig, $validator);
        $client = (new ServiceFactory($config['service']))->createClient();

        return new RedmineRegistrar(
            new IssueFactory(
                new EntityResolver($client, $generalIssueConfig->getProjectIdentifier()),
                $generalIssueConfig,
                $this->issueSupporter,
                new UserResolver($client),
            ),
            new IssueApiClient($client),
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
            throw new ConfigValidationException($violations, '[Redmine] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
