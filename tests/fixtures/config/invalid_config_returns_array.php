<?php

declare(strict_types=1);

// Invalid: returns array instead of GeneralConfigInterface
return [
    'paths' => ['in' => __DIR__],
    'registrar' => ['type' => 'github'],
];

