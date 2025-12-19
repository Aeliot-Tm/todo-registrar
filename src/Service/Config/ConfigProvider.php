<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Config;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class ConfigProvider
{
    public function __construct(
        private ConfigFileDetector $configFileDetector,
        private ConfigFactory $configFactory,
    ) {
    }

    public function getConfig(?string $path): GeneralConfigInterface
    {
        $path = $this->configFileDetector->getPath($path);
        if ('php' === strtolower(pathinfo($path, \PATHINFO_EXTENSION))) {
            return require $path;
        }

        return $this->configFactory->create($path);
    }
}
