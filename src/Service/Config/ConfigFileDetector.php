<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Config;

/**
 * @internal
 */
final readonly class ConfigFileDetector
{
    public function __construct(
        private AbsolutePathMaker $absolutePathMaker,
        private ConfigFileGuesser $configFileGuesser,
    ) {
    }

    public function getPath(?string $path): string
    {
        if ($path) {
            $path = $this->absolutePathMaker->prepare($path);
        }
        $path ??= $this->configFileGuesser->guess();

        if (!file_exists($path)) {
            throw new \RuntimeException(\sprintf('Config file "%s" does not exist', $path));
        }

        return $path;
    }
}
