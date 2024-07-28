<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->ignoreVCS(true)
    ->in(__DIR__)
    ->exclude(['tests/fixtures', 'var', 'vendor'])
    ->append([__DIR__ . '/bin/todo-registrar']);
