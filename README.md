# TODO Registrar

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=black)](https://packagist.org/packages/aeliot/todo-registrar)
[![GitHub License](https://img.shields.io/github/license/Aeliot-Tm/todo-registrar?label=License&labelColor=black)](LICENSE)
[![WFS](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated_testing.yml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions)
[![GitHub Issues or Pull Requests](https://img.shields.io/github/issues/Aeliot-Tm/todo-registrar?labelColor=black&label=Issues)](https://github.com/Aeliot-Tm/todo-registrar/issues)

Package responsible for registration of issues in Issue Trackers.

## Installation

1. Require package via Composer:
   ```shell
   composer require --dev aeliot/todo-registrar
   ```
2. Create configuration file. It expects ".todo-registrar.php" or ".todo-registrar.dist.php" at the root of the project.

## Using

1. Call script:
   ```shell
   vendor/bin/todo-registrar
   ```
   You may pass option with path to config `--config=/custom/path/to/config`.
   Otherwise, it tries to use one of default paths to config file.
2. Commit updated files. You may config your pipeline/job on CI which commits updates.

## Configuration file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
It may be put in any other place, but you have to define path to it with option `--config=/custom/path/to/cofig`
while call the script. Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`.

[See full documentation about config](docs/config.md)

## Supported todo-tags

It detects `TODO` and `FIXME` by default. But you may config your custom set of tags in config file.
Whey will be detected case insensitively.

## Supported formats of comments:

It detects TODO-tags in single-line comments started with both `//` and `#` symbols
and multiple-line comments `/* ... */` and phpDoc `/** ... **/`.

Which can be formatted differently:
```php
// TODO: comment summary
// TODO comment summary
// TODO@assigne: comment summary

/**
 * TODO: XX-001 comment summary
 *       with some complex description
 */
```

And others. [See all supported formats](docs/supported_patters_of_comments.md).

## Supported Issue Trackers

Currently, todo-registrar supports the following issue trackers:

| Issue Tracker                                   | Description                                                                                 |
|-------------------------------------------------|---------------------------------------------------------------------------------------------|
| [Jira](https://www.atlassian.com/software/jira) | Supported via API tokens. See [description of configuration](docs/registrar/jira/config.md) |
