<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\InlineConfig;

use Aeliot\TodoRegistrar\Exception\InvalidInlineConfigFormatException;
use Aeliot\TodoRegistrar\InlineConfigReaderInterface;

final class ExtrasReader implements InlineConfigReaderInterface
{
    public function __construct(private ArrayFromJsonLikeLexerBuilder $arrayBuilder)
    {
    }

    /**
     * @return array<array-key,mixed>
     */
    public function getInlineConfig(string $input): array
    {
        preg_match('/\{\s*EXTRAS\s*:/ui', $input, $matches, PREG_OFFSET_CAPTURE);
        if (!$matches) {
            return [];
        }

        $data = $this->arrayBuilder->build(new JsonLikeLexer($input, (int) $matches[0][1]));
        if (count($data) !== 1) {
            throw new InvalidInlineConfigFormatException('EXTRAS must contain one and only one element');
        }

        $config = reset($data);
        if (!$config || !\is_array($config) || array_is_list($config)) {
            throw new InvalidInlineConfigFormatException('EXTRAS must contain object');
        }

        return $config;
    }
}
