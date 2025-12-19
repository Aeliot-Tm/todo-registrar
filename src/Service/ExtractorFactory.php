<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service;

use Aeliot\TodoRegistrar\Contracts\GeneralConfigInterface;
use Aeliot\TodoRegistrar\Service\Comment\Extractor;
use Aeliot\TodoRegistrar\Service\Tag\Detector as TagDetector;

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
