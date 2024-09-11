<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->ignoreVCS(true)
    ->in(dirname(__DIR__, 2))
    ->exclude(['tests/fixtures', 'var', 'vendor'])
    ->append([dirname(__DIR__, 2) . '/bin/todo-registrar']);
