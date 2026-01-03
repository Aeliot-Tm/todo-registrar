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

use Aeliot\TodoRegistrarContracts\RegistrarInterface;
use Aeliot\TodoRegistrarContracts\TodoInterface;

/**
 * @internal
 */
final readonly class YandexTrackerRegistrar implements RegistrarInterface
{
    public function __construct(
        private IssueFactory $issueFactory,
    ) {
    }

    public function register(TodoInterface $todo): string
    {
        $request = $this->issueFactory->create($todo);
        $response = $request->send();

        /** @var string $key */
        $key = $response->getField('key');

        return $key;
    }
}
