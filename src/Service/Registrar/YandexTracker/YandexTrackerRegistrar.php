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

use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use Aeliot\TodoRegistrarContracts\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\Todo\TodoInterface;

/**
 * @internal
 */
final readonly class YandexTrackerRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFactory $issueFactory,
    ) {
    }

    /**
     * @throws UnexpectedResponseException
     */
    public function register(TodoInterface $todo): string
    {
        $request = $this->issueFactory->create($todo);
        try {
            $response = $request->send();
        } catch (\Throwable $exception) {
            throw new UnexpectedResponseException('Cannot create ticket in Yandex Tracker', 0, $exception);
        }

        /** @var string $key */
        $key = $response->getField('key');

        return $key;
    }
}
