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

namespace Aeliot\TodoRegistrar\Service\Registrar\YandexTracker;

use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\ValidatorFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTaggedItem(index: RegistrarType::YandexTracker->value)]
final class YandexTrackerRegistrarFactory implements RegistrarFactoryInterface
{
    public function create(array $config, ?ValidatorInterface $validator = null): RegistrarInterface
    {
        $validator ??= ValidatorFactory::create();
        $issueConfig = ($config['issue'] ?? []) + ['queue' => $config['queue'] ?? ''];
        $generalIssueConfig = $this->createGeneralIssueConfig($issueConfig, $validator);

        $apiClientFactory = new ApiClientFactory($config['service']);
        // Initialize the Client singleton with credentials
        $apiClientFactory->createTracker();

        return new YandexTrackerRegistrar(
            new IssueFactory($generalIssueConfig),
        );
    }

    /**
     * @param array<string, mixed> $issueConfig
     */
    public function createGeneralIssueConfig(array $issueConfig, ValidatorInterface $validator): GeneralIssueConfig
    {
        $generalIssueConfig = new GeneralIssueConfig($issueConfig);
        $violations = $validator->validate($generalIssueConfig);
        if (\count($violations) > 0) {
            throw new ConfigValidationException($violations, '[YandexTracker] Invalid general issue config');
        }

        return $generalIssueConfig;
    }
}
