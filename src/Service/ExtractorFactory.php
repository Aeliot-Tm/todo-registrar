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

use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;
use Aeliot\TodoRegistrarContracts\GeneralConfigInterface;

/**
 * @internal
 */
final readonly class ExtractorFactory
{
    public function create(GeneralConfigInterface $config): Extractor
    {
        return new Extractor(new TagDetector($config->getTags()));
    }
}
