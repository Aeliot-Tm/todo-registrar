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

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractGeneralIssueConfig
{
    #[Assert\NotNull(message: 'Option "addTagToLabels" is required')]
    #[Assert\Type(type: 'bool', message: 'Option "addTagToLabels" must be a boolean value')]
    protected mixed $addTagToLabels = null;

    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "labels" is required'),
        new Assert\Type(type: 'array', message: 'Option "labels" must be an array'),
        new Assert\All([new Assert\Type(type: 'string', message: 'Each label must be a string')]),
    ])]
    protected mixed $labels = null;

    #[Assert\NotNull(message: 'Option "tagPrefix" is required')]
    #[Assert\Type(type: 'string', message: 'Option "tagPrefix" must be a string')]
    protected mixed $tagPrefix = null;

    #[Assert\NotNull(message: 'Option "summaryPrefix" is required')]
    #[Assert\Type(type: 'string', message: 'Option "summaryPrefix" must be a string')]
    protected mixed $summaryPrefix = null;

    #[Assert\IsNull(message: 'Unknown configuration options detected: {{ value }}')]
    protected mixed $invalidKeys = null;

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
        $propertyNames = array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            (new \ReflectionClass($this))->getProperties()
        );

        foreach ($propertyNames as $key) {
            if (!\array_key_exists($key, $config)) {
                continue;
            }

            $this->$key = $config[$key];
            unset($config[$key]);
        }

        if ($config) {
            $this->invalidKeys = implode(', ', array_keys($config));
        }
    }
}
