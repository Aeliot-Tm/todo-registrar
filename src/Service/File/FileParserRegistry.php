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

namespace Aeliot\TodoRegistrar\Service\File;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class FileParserRegistry
{
    public function __construct(
        #[AutowireLocator('aeliot.todo_registrar.file_parser')]
        private ServiceLocator $parsers,
    ) {
    }

    public function findParser(\SplFileInfo $file): ?FileParserInterface
    {
        $extension = strtolower($file->getExtension());
        if (!$this->parsers->has($extension)) {
            return null;
        }

        return $this->parsers->get($extension);
    }
}
