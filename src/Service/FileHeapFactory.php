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

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Dto\FileHeap;
use Aeliot\TodoRegistrar\Dto\HeapContext;
use Aeliot\TodoRegistrar\Exception\FileReadException;
use Aeliot\TodoRegistrar\Exception\LogicException;
use Aeliot\TodoRegistrar\Service\Comment\CommentNodesBuilder;
use Aeliot\TodoRegistrar\Service\Comment\SequentialCommentGlueGateRegistry;
use Aeliot\TodoRegistrar\Service\File\FileParserRegistry;
use Aeliot\TodoRegistrar\Service\File\Saver;

/**
 * @internal
 */
final readonly class FileHeapFactory
{
    public function __construct(
        private CommentNodesBuilder $commentNodesBuilder,
        private FileParserRegistry $fileParserRegistry,
        private SequentialCommentGlueGateRegistry $glueGateRegistry,
        private Saver $saver,
    ) {
    }

    /**
     * @throws FileReadException
     * @throws LogicException
     */
    public function create(\SplFileInfo $file, HeapContext $context): ?FileHeap
    {
        $extension = strtolower($file->getExtension());
        $extensionAlias = $context->extensionAliases[$extension] ?? $extension;
        $fileParser = $this->fileParserRegistry->findParser($extensionAlias);
        if (!$fileParser) {
            $context->output->writeErr("There is not configured parser for file: {$file->getPathname()}", OutputAdapter::VERBOSITY_NORMAL);

            return null;
        }

        $context->output->writeln("Begin process file: {$file->getPathname()}", OutputAdapter::VERBOSITY_DEBUG);
        $glueGate = null;
        if ($context->glueSequentialComments) {
            $glueGate = $this->glueGateRegistry->find($extensionAlias);
            if (null === $glueGate) {
                throw new LogicException(\sprintf('Sequential comment glue is enabled but no glue gate is configured for extension alias "%s" (file: %s)', $extensionAlias, $file->getPathname()));
            }
        }

        $fileHeap = new FileHeap(
            $this->commentNodesBuilder,
            $fileParser->parse($file),
            $context->glueSequentialComments,
            $glueGate,
            $context->statistic,
            $this->saver,
        );

        if ($context->output->isDebug()) {
            $context->output->writeln('Detected comment nodes: ' . \count($fileHeap->getCommentNodes()));
        }

        return $fileHeap;
    }
}
