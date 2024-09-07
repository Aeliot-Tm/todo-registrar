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
        preg_match('/\{\s*EXTRAS\s*:/ui', $input, $matches, \PREG_OFFSET_CAPTURE);
        if (!$matches) {
            return [];
        }

        $firstPosition = (int) $matches[0][1];
        $lastPosition = strrpos($input, '}');
        $inlineConfigString = substr($input, $firstPosition, $lastPosition - $firstPosition + 1);
        $data = $this->arrayBuilder->build(new JsonLikeLexer($inlineConfigString, 0));
        if (1 !== \count($data)) {
            throw new InvalidInlineConfigFormatException('EXTRAS must contain one and only one element');
        }

        $config = reset($data);
        if (!$config || !\is_array($config) || array_is_list($config)) {
            throw new InvalidInlineConfigFormatException('EXTRAS must contain object');
        }

        return $config;
    }
}
