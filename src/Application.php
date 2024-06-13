<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar;

use Aeliot\TodoRegistrar\Service\File\Finder;
use Aeliot\TodoRegistrar\Service\FileProcessor;

class Application
{
    public function __construct(
        private Finder $finder,
        private FileProcessor $fileProcessor,
    ) {
    }

    public function run(): void
    {
        foreach ($this->finder as $file) {
            $this->fileProcessor->process($file);
        }
    }
}