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

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;

abstract class AbstractIssueConfig
{
    protected bool $addTagToLabels;
    /**
     * @var string[]
     */
    protected array $labels;
    protected string $tagPrefix;
    protected ?string $summaryPrefix;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->setProperties($this->normalizeConfig($config));
    }

    public function isAddTagToLabels(): bool
    {
        return $this->addTagToLabels;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getSummaryPrefix(): string
    {
        return $this->summaryPrefix;
    }

    public function getTagPrefix(): string
    {
        return $this->tagPrefix;
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return array<string,mixed>
     */
    protected function normalizeConfig(array $config): array
    {
        $config += [
            'addTagToLabels' => false,
            'labels' => [],
            'summaryPrefix' => '',
            'tagPrefix' => '',
        ];

        $config['addTagToLabels'] = (bool) $config['addTagToLabels'];
        $config['labels'] = (array) $config['labels'];

        return $config;
    }

    /**
     * @param array<string,mixed> $config
     */
    protected function setProperties(array $config): void
    {
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            $key = $property->getName();
            if (!\array_key_exists($key, $config)) {
                throw new InvalidConfigException("Undefined property of issue config: {$key}");
            }

            $this->$key = $config[$key];
            unset($config[$key]);
        }

        if ($config) {
            $invalidKeys = implode(', ', array_keys($config));
            throw new InvalidConfigException("Not supported config for issues detected: $invalidKeys");
        }
    }
}
