# TODO Registrar

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
   You may pass option with it `--config=/custom/path/to/config`. Otherwise, it tries to use one of default files. 
2. Commit updated files. You may config your pipeline/job on CI which commits updates.

## Configuration file

Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`. See [example](.todo-registrar.dist.php).

It has 2 setters:
1. `setFinder` - accepts instance of configured finder of php-files.
2. `setRegistrar` - responsible for configuration of registrar factory. It accepts as type of registrar with its config
   as instans of custom registrar factory.

## Supported Issue Trackers

Currently, todo-registrar supports the following issue trackers:

| Issue Tracker                                   | Description              |
|-------------------------------------------------|--------------------------|
| [Jira](https://www.atlassian.com/software/jira) | Supported via API tokens |
