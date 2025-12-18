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

use Aeliot\TodoRegistrar\Service\File\Saver;
use Aeliot\TodoRegistrar\Service\File\Tokenizer;
use Symfony\Component\Console\Output\OutputInterface;

class FileProcessor
{
    public function __construct(
        private CommentRegistrar $commentRegistrar,
        private Saver $saver,
        private Tokenizer $tokenizer,
    ) {
    }

    public function process(\SplFileInfo $file, OutputInterface $output): int
    {
        $tokens = $this->tokenizer->tokenize($file);
        $countNewTodos = $this->commentRegistrar->register($tokens, $output);
        if ($countNewTodos) {
            if ($output->isDebug()) {
                $output->writeln("Save changes of file: {$file->getPathname()}");
            }
            $this->saver->save($file, $tokens);
        } elseif ($output->isDebug()) {
            $output->writeln("No one TODO registered for file: {$file->getPathname()}");
        }

        return $countNewTodos;
    }
}
