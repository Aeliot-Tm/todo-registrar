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

class FileProcessor
{
    public function __construct(
        private CommentRegistrar $commentRegistrar,
        private Saver $saver,
        private Tokenizer $tokenizer,
    ) {
    }

    public function process(\SplFileInfo $file): void
    {
        $tokens = $this->tokenizer->tokenize($file);
        if (!$this->commentRegistrar->register($tokens)) {
            return;
        }

        $this->saver->save($file, $tokens);
    }
}
