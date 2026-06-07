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

use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

/**
 * @internal
 */
final readonly class JiraRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFieldFactory $issueFieldFactory,
        private IssueLinkRegistrar $issueLinkRegistrar,
        private IssueService $issueService,
    ) {
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidInlineConfigFormatException
     * @throws LogicException
     * @throws UnexpectedResponseException
     */
    public function register(TodoInterface $todo): string
    {
        $issueField = $this->issueFieldFactory->create($todo);

        try {
            $issueKey = $this->issueService->create($issueField)->key;
        } catch (JiraException $exception) {
            throw new UnexpectedResponseException('Cannot create ticket in JIRA', 0, $exception);
        } catch (\JsonMapper_Exception $exception) {
            throw new LogicException('Unexpected behavior of JIRA integration', 0, $exception);
        }

        $this->issueLinkRegistrar->registerLinks($issueKey, $todo);

        return $issueKey;
    }
}
