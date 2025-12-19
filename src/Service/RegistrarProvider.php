<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;
use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;

/**
 * @internal
 */
final readonly class RegistrarProvider
{
    public function __construct(
        private RegistrarFactoryRegistry $registrarFactoryRegistry,
    ) {
    }

    public function getRegistrar(GeneralConfigInterface $config): RegistrarInterface
    {
        $registrarType = $config->getRegistrarType();

        if (\is_string($registrarType)) {
            if (class_exists($registrarType) && is_a($registrarType, RegistrarFactoryInterface::class, true)) {
                $registrarType = new $registrarType();
            } else {
                $newType = RegistrarType::tryFrom($registrarType);
                if (!$newType) {
                    throw new InvalidConfigException(\sprintf('Invalid type of registrar: %s', $registrarType));
                }
                $registrarType = $newType;
            }
        }

        if ($registrarType instanceof RegistrarFactoryInterface) {
            $registrarFactory = $registrarType;
        } else {
            $registrarFactory = $this->registrarFactoryRegistry->getFactory($registrarType);
        }

        return $registrarFactory->create($config->getRegistrarConfig());
    }
}
