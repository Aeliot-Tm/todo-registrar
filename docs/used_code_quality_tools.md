Used Code Quality Tools
=======================

The next Code Quality Tools are used by project

### Composer Requirements Checker

Package [composer-require-checker](https://github.com/maglnet/ComposerRequireChecker) checks
that all necessary requirements are added obviously into [composer.json](../composer.json) file.

Call script to use:
```shell
composer require-check
```

### Composer Unused

Package [composer-unused](https://github.com/composer-unused/composer-unused) checks
that file [composer.json](../composer.json) does not contain unused requirements.

Call script to use:
```shell
composer unused
```

### PHP CS Fixer

Package [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) checks and fixes code style.

1. Call script to check your code style:
   ```shell
   composer cs-check
   ```
2. Call script to fix your code style:
   ```shell
   composer cs-fix
   ```

### PHPStan

Package [PHPStan](https://phpstan.org/) is responsible for the static analysis of code of the project

Call script to use:
```shell
composer phpstan
```

### PHPUnit

Package [PHPUnit](https://phpunit.de/index.html) is testing framework. Used to write tests.

Call script to run test:
```shell
composer phpunit
```

### Security vulnerabilities check

Use built in command:
```shell
composer audit
```
