<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\File\Finder;

/**
 * TODO: use component symfony/validator to show all violations of array config
 *
 * @internal
 */
final class ArrayConfigFactory
{
    /**
     * @param array<string,mixed> $options
     */
    public function create(array $options): Config
    {
        $this->validate($options);
        $config = (new Config())
            ->setFinder($this->createFinder($options))
            ->setRegistrar($options['registrar']['type'], $options['registrar']['options'] ?? []);

        $tags = $options['tags'] ?? null;
        if ($tags) {
            $config->setTags((array) $tags);
        }

        return $config;
    }

    /**
     * @param array<string,mixed> $options
     */
    private function createFinder(array $options): Finder
    {
        $finder = (new Finder())
            ->files()
            ->ignoreVCS(true)
            ->in($options['paths']['in'] ?? getcwd());

        $append = $options['paths']['append'] ?? null;
        if ($append) {
            $finder->append((array) $append);
        }

        $exclude = $options['paths']['exclude'] ?? null;
        if ($exclude) {
            $finder->exclude((array) $exclude);
        }

        return $finder;
    }

    private function validate(array $options): void
    {
        if (!isset($options['registrar']['type'])) {
            throw new InvalidConfigException('Missed type of registrar');
        }

        if (!\is_string($options['registrar']['type'])) {
            throw new InvalidConfigException('Type of registrar must be the string');
        }
    }
}