<?php

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'class_definition' => [
        'multi_line_extends_each_single_line' => true,
        'single_line' => false,
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'header_comment' => [
        'header' => <<<'EOF'
            This file is part of the TODO Registrar project.

            (c) Anatoliy Melnikov <5785276@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            EOF,
    ],
    'phpdoc_align' => ['align' => 'left'],
];

$config = (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->setRules($rules);

/** @var PhpCsFixer\Finder $finder */
$finder = require __DIR__ . '/.php-cs-fixer-finder.php';

return $config->setFinder($finder);
