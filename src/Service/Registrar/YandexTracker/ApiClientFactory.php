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

use BugrovWeb\YandexTracker\Api\Tracker;

/**
 * @internal
 */
final readonly class ApiClientFactory
{
    /**
     * @param array{token: string, orgId: string} $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    public function createTracker(): Tracker
    {
        return new Tracker($this->config['token'], $this->config['orgId']);
    }
}
