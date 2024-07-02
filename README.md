# TODO Registrar

Package is responsible for registration of your TODO/FIXME and other notes in code as issues in Issue Trackers like
JIRA.
It injects IDs/Keys of created issues into proper comments in code. So, they will not be created twice when you commit
changes.

So, you don't spend time to fill lots of fields in issue-tracking system and lots of times for each issue.
After that you may use all power of management to plan solving of lacks of your code.
And injected marks helps to find proper places in code quickly.

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

## Configuration file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
It may be put in any other place, but you have to define path to it with option `--config=/custom/path/to/cofig`
while call the script. Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`.

[See full documentation about config](docs/config.md)

## Inline Configuration

Script supports inline configuration of each TODO-comment. It helps flexibly configure different aspects of created issues.
Like relations to other issues, labels, components and so on. So, it becomes very powerful instrument. 😊

[See documentation about inline config](docs/inline_config.md)
