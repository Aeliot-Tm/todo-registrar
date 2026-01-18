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
    #[Assert\Type(type: 'bool', message: 'Option "addTagToLabels" must be a boolean value')]
    protected mixed $addTagToLabels = false;

    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "allowedLabels" cannot be null'),
        new Assert\Type(type: 'array', message: 'Option "allowedLabels" must be an array'),
        new Assert\All([
            new Assert\Sequentially([
                new Assert\NotNull(message: 'Each allowed label cannot be null'),
                new Assert\Type(type: 'string', message: 'Each allowed label must be a string'),
            ]),
        ]),
    ])]
    protected mixed $allowedLabels = [];

    #[Assert\Type(type: 'bool', message: 'Option "showContext" must be a boolean value')]
    protected mixed $showContext = false;

    #[Assert\IsNull(message: 'Unknown configuration options detected: {{ value }}')]
    protected mixed $invalidKeys = null;

    /**
     * @var string[]|null
     */
    #[Assert\Sequentially([
        new Assert\NotNull(message: 'Option "labels" cannot be null'),
        new Assert\Type(type: 'array', message: 'Option "labels" must be an array'),
        new Assert\All([
            new Assert\Sequentially([
                new Assert\NotNull(message: 'Each label cannot be null'),
                new Assert\Type(type: 'string', message: 'Each label must be a string'),
            ]),
        ]),
    ])]
    protected mixed $labels = [];

    #[Assert\Type(type: 'string', message: 'Option "summaryPrefix" must be a string')]
    protected mixed $summaryPrefix = null;

    #[Assert\Type(type: 'string', message: 'Option "tagPrefix" must be a string')]
    protected mixed $tagPrefix = null;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->setProperties($this->normalizeConfig($config));
    }

    /**
     * @return string[]
     */
    public function getAllowedLabels(): array
    {
        return $this->allowedLabels;
    }

    public function isAddTagToLabels(): bool
    {
        return (bool) $this->addTagToLabels;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function isShowContext(): bool
    {
        return (bool) $this->showContext;
    }

    public function getSummaryPrefix(): string
    {
        return (string) $this->summaryPrefix;
    }

    public function getTagPrefix(): string
    {
        return (string) $this->tagPrefix;
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
            'allowedLabels' => [],
            'showContext' => false,
            'summaryPrefix' => '',
            'tagPrefix' => '',
        ];

        $config['addTagToLabels'] = (bool) $config['addTagToLabels'];
        $config['labels'] = (array) $config['labels'];
        $config['allowedLabels'] = (array) $config['allowedLabels'];
        $config['showContext'] = (bool) $config['showContext'];

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
