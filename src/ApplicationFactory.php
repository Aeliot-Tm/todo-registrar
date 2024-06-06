<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Service\Comment\Detector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\CommentRegistrar;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use Aeliot\TodoRegistrar\Service\FileProcessor;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

final class ApplicationFactory
{
    public function create(Config $config): Application
    {
        $registrar = $this->createRegistrar($config);
        $commentRegistrar = $this->createCommentRegistrar($registrar);
        $fileProcessor = $this->createFileProcessor($commentRegistrar);

        return new Application(
            $config->getFinder(),
            $fileProcessor,
        );
    }

    private function createCommentRegistrar(RegistrarInterface $registrar): CommentRegistrar
    {
        return new CommentRegistrar(
            new Detector(),
            new Extractor(),
            $registrar,
        );
    }

    private function createFileProcessor(CommentRegistrar $commentRegistrar): FileProcessor
    {
        return new FileProcessor(
            $commentRegistrar,
            new Saver(),
            new Tokenizer(),
        );
    }

    private function createRegistrar(Config $config): RegistrarInterface
    {
        return (new RegistrarFactory())->createRegistrar(
            $config->getRegistrarType(),
            $config->getRegistrarConfig(),
        );
    }
}