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

final class ColorGenerator
{
    /**
     * Generate color for name (label).
     * Uses hash of the name to ensure deterministic color generation.
     * Color values are in range 25% to 85% for each RGB channel.
     *
     * @return string Hex color code (e.g., "#A1B2C3")
     */
    public function generateColor(string $name): string
    {
        // Use hash of the name for deterministic color generation
        $hash = md5($name);
        // Extract 3 bytes from hash (one for each RGB channel)
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        // Map values from 0-255 range to 25%-85% range (64-217)
        $minValue = 64; // 25% of 255
        $maxValue = 217; // 85% of 255
        $range = $maxValue - $minValue;

        $r = $minValue + (int) (($r / 255) * $range);
        $g = $minValue + (int) (($g / 255) * $range);
        $b = $minValue + (int) (($b / 255) * $range);

        return \sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
