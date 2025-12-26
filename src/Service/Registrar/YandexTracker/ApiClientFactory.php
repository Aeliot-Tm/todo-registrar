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

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use BugrovWeb\YandexTracker\Api\Tracker;

/**
 * @internal
 */
final readonly class ApiClientFactory
{
    /**
     * @param array{token: string, orgId: string, isCloud?: bool} $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    public function createTracker(): Tracker
    {
        return new Tracker(
            $this->config['token'],
            $this->config['orgId'],
            $this->isCloud(),
        );
    }

    private function isCloud(): bool
    {
        return match ($this->config['isCloud'] ?? true) {
            false, 'false', 0, '0', 'n', 'no' => false,
            true, 'true', 1, '1', 'y', 'yes' => true,
            default => throw new InvalidConfigException('Unexpected value of option "isCloud"'),
        };
    }
}
