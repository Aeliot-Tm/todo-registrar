<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;

/**
 * @internal
 */
final readonly class FileProcessorFactory
{
    public function __construct(
        private CommentRegistrarFactory $commentRegistrarFactory,
        private RegistrarProvider $registrarProvider,
        private Saver $saver,
        private Tokenizer $tokenizer,
    ) {
    }

    public function create(GeneralConfigInterface $config): FileProcessor
    {
        $registrar = $this->registrarProvider->getRegistrar($config);
        $commentRegistrar = $this->commentRegistrarFactory->createCommentRegistrar($registrar, $config);

        return new FileProcessor($commentRegistrar, $this->saver, $this->tokenizer);
    }
}
