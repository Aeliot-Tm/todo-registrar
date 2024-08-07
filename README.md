# TODO Registrar

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=black)](https://packagist.org/packages/aeliot/todo-registrar)
[![WFS](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated_testing.yml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions)
[![GitHub Issues or Pull Requests](https://img.shields.io/github/issues/Aeliot-Tm/todo-registrar?labelColor=black&label=Issues)](https://github.com/Aeliot-Tm/todo-registrar/issues)
[![GitHub License](https://img.shields.io/github/license/Aeliot-Tm/todo-registrar?label=License&labelColor=black)](LICENSE)

It takes TODO/FIXME and other comments from your php-code and register them as issues in Issue Trackers like
JIRA. With all necessary labels, linked issues and so on.

## Motivation

Time to time developers left notes in code to not forget to do something. And they forget to do it. One of the most reason is that it is difficult to manage them.

Why do developers left comment in code instead of registering of isdues? It is convenient. You don't need to deal with UI of Issue Tracker and to fill lots of field. And lots of times to register each issue. It takes time. The second reason, comment in code permit to mark exact place which have to be modified. And many other reasons. No matter why they do it. They do it and leave this comments for years.

Somebody have to manage it.

So, we need in tool which will be responsible for registering of issues and save time of developers. After that you may use all power of management to plan solving of lacks of your code.

This script do it for you. It registers issues with all necessary params. Then injects IDs/Keys of created issues into comment in code. This prevents creating of issues twice and injected marks helps to find proper places in code quickly.

## Installation

1. Require package via Composer:
   ```shell
   composer require --dev aeliot/todo-registrar
   ```
2. Create [configuration file](docs/config.md). It expects ".todo-registrar.php" or ".todo-registrar.dist.php" at the root of the project.

## Using

1. Call script:
   ```shell
   vendor/bin/todo-registrar
   ```
   You may pass option with path to config `--config=/custom/path/to/config`.
   Otherwise, it tries to use one of default paths to [config file](docs/config.md).
2. Commit updated files. You may config your pipeline/job on CI which commits updates.

## Configuration file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
It may be put in any other place, but you have to define path to it with option `--config=/custom/path/to/cofig`
while call the script. Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`.

[See full documentation about config](docs/config.md)

## Inline Configuration

Script supports inline configuration of each TODO-comment. It helps flexibly configure different aspects of created issues.
Like relations to other issues, labels, components and so on. So, it becomes very powerful instrument. 😊

[See documentation about inline config](docs/inline_config.md)

## Supported todo-tags

It detects `TODO` and `FIXME` by default. But you may config your custom set of tags in config file.
Whey will be detected case insensitively.

## Supported formats of comments:

It detects TODO-tags in single-line comments started with both `//` and `#` symbols
and multiple-line comments `/* ... */` and phpDoc `/** ... **/`.

Comments can be formatted differently:
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

