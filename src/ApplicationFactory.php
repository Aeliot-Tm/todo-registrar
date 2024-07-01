<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Service\Comment\Detector;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\CommentRegistrar;
use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use Aeliot\TodoRegistrar\Service\FileProcessor;
use Aeliot\TodoRegistrar\Service\InlineConfig\ArrayFromJsonLikeLexerBuilder;
use Aeliot\TodoRegistrar\Service\InlineConfig\ExtrasReader;
use Aeliot\TodoRegistrar\Service\InlineConfig\InlineConfigFactory;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryRegistry;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use Aeliot\TodoRegistrar\Service\TodoFactory;

class ApplicationFactory
{
    public function create(Config $config): Application
    {
        $registrar = $this->createRegistrar($config);
        $commentRegistrar = $this->createCommentRegistrar($registrar, $config);
        $fileProcessor = $this->createFileProcessor($commentRegistrar);

        return new Application(
            $config->getFinder(),
            $fileProcessor,
        );
    }

    private function createCommentRegistrar(RegistrarInterface $registrar, Config $config): CommentRegistrar
    {
        $inlineConfigReader = $config->getInlineConfigReader() ?? new ExtrasReader(new ArrayFromJsonLikeLexerBuilder());
        $inlineConfigFactory = $config->getInlineConfigFactory() ?? new InlineConfigFactory();

        return new CommentRegistrar(
            new Detector(),
            new Extractor(new TagDetector($config->getTags())),
            $registrar,
            new TodoFactory($inlineConfigFactory, $inlineConfigReader),
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
        $registrarType = $config->getRegistrarType();
        if ($registrarType instanceof RegistrarFactoryInterface) {
            $registrarFactory = $registrarType;
        } else {
            $registrarFactory = (new RegistrarFactoryRegistry())->getFactory($registrarType);
        }

        return $registrarFactory->create($config->getRegistrarConfig());
    }
}
