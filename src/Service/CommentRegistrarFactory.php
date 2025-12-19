<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Contracts\RegistrarInterface;
use Aeliot\TodoRegistrar\Service\Comment\Detector;

/**
 * @internal
 */
final readonly class CommentRegistrarFactory
{
    public function __construct(
        private Detector $detector,
        private ExtractorFactory $extractorFactory,
        private TodoFactoryFactory $todoFactoryFactory,
    ) {
    }

    public function createCommentRegistrar(
        RegistrarInterface $registrar,
        GeneralConfigInterface $config,
    ): CommentRegistrar {

        return new CommentRegistrar(
            $this->detector,
            $this->extractorFactory->create($config),
            $registrar,
            $this->todoFactoryFactory->create($config),
        );
    }
}
