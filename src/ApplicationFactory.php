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

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ApplicationFactory
{
    public function create(?string $configPath, OutputInterface $output): Application
    {
        $config = $this->getConfig($configPath);
        $fileProcessor = $this->getProcessor($config);

        return new Application(
            $config->getFinder(),
            $fileProcessor,
            new OutputAdapter($output),
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
            $registrarFactory = (new RegistrarFactoryRegistry())->getFactory($registrarType);
        }

        return $registrarFactory->create($config->getRegistrarConfig());
    }

    private function getConfig(?string $configPath): Config
    {
        $absolutePathMaker = new AbsolutePathMaker();
        $path = $configPath;
        if ($path) {
            $path = $absolutePathMaker->prepare($path);
        }
        $path ??= (new ConfigFileGuesser($absolutePathMaker))->guess();

        return (new ConfigFactory(new ArrayConfigFactory()))->create($path);
    }

    private function getProcessor(Config $config): FileProcessor
    {
        $registrar = $this->createRegistrar($config);
        $commentRegistrar = $this->createCommentRegistrar($registrar, $config);

        return $this->createFileProcessor($commentRegistrar);
    }
}
