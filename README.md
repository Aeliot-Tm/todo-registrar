![logo](docs/logo.svg)

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=black)](https://packagist.org/packages/aeliot/todo-registrar)
[![WFS](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated_testing.yml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions)
[![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/Aeliot-Tm/todo-registrar?label=Maintainability&labelColor=black)](https://codeclimate.com/github/Aeliot-Tm/todo-registrar)
[![GitHub Issues or Pull Requests](https://img.shields.io/github/issues-pr-closed/Aeliot-Tm/todo-registrar?label=Pull%20Requests&labelColor=black)](https://github.com/Aeliot-Tm/todo-registrar/pulls?q=is%3Apr+is%3Aclosed)
[![GitHub License](https://img.shields.io/github/license/Aeliot-Tm/todo-registrar?label=License&labelColor=black)](LICENSE)

It takes TODO/FIXME and other comments from your php-code and register them as issues in Issue Trackers like
JIRA. With all necessary labels, linked issues and so on.

## Motivation

Time to time developers left notes in code to not forget to do something. And they forget to do it.
One of the main reason is that it is difficult to manage them.

Why do developers left comment in code instead of registering of issues? It is convenient. You don't need to deal
with UI of Issue Tracker and to fill lots of field. And lots of times to register each issue. It takes time.
The second reason, comment in code permit to mark exact place which have to be modified. And many other reasons.
No matter why they do it. They do it and leave this comments for years.

Somebody have to manage it.

So, we need in tool which will be responsible for registering of issues and save time of developers. After that
you may use all power of management to plan solving of lacks of your code.

This script do it for you. It registers issues with all necessary params. Then injects IDs/Keys of created issues
into comment in code. This prevents creating of issues twice and injected marks helps to find proper places in code quickly.

## Installation

There are few ways of installation:
1. [Phive](#phive)
2. [Composer](#composer)
3. [Downloading of PHAR directly](#downloading-of-phar-directly)
4. [Docker](#docker)

#### Phive

You can install this package with [Phive](https://phar.io/). It permits you to install package by one console command
without extending dependencies in your composer-files.
```shell
phive install todo-registrar
```

Sometimes you may need to update database of package-aliases of PHIVE. See [issue #3](https://github.com/Aeliot-Tm/php-cs-fixer-baseline/issues/3)
So, just call console command for it:
```shell
phive update-repository-list
```

To upgrade this package use the following command:
```shell
phive update todo-registrar
```

#### Composer

You can install this package with [Composer](https://getcomposer.org/doc/03-cli.md#install-i):
```shell
composer require --dev aeliot/todo-registrar
```

#### Downloading of PHAR directly

Download PHAR directly to root directory of the project or in another place as you wish.
```shell
# Do adjust the URL if you need a release other than the latest
wget -O todo-registrar.phar "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar"
wget -O todo-registrar.phar.asc "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar.asc"

# Check that the signature matches
gpg --verify todo-registrar.phar.asc todo-registrar.phar

# Check the issuer (the ID can also be found from the previous command)
gpg --keyserver hkps://keys.openpgp.org --recv-keys 9D0DD6FCB92C84688B777DF59204DEE8CAE9C22C

rm todo-registrar.phar.asc
chmod +x todo-registrar.phar
```

#### Docker

You can use the pre-built Docker image from GitHub Container Registry:

```shell
# Pull the latest image
docker pull ghcr.io/aeliot-tm/todo-registrar:latest

# Or use a specific version tag
docker pull ghcr.io/aeliot-tm/todo-registrar:v1.8.0
```

To analyze your project, mount your code directory and configuration file:

```shell
# Basic usage (config file should be in project root)
docker run --rm \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest

# With custom config file path
docker run --rm \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest \
  --config=/code/.todo-registrar.yaml

# With verbose output
docker run --rm \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest \
  --config=/code/.todo-registrar.yaml \
  --verbose=very_verbose
```

**Important notes:**
- Mount your project directory to `/code` (this is the working directory inside the container)
- The config file can be inside your project directory (will be found automatically) or mounted separately
- Use `-it` flags for interactive mode if you need to see real-time output
- The container uses unbuffered output, so verbose messages will appear in real-time

## Using

### Command Line

1. Call script:
   ```shell
   vendor/bin/todo-registrar
   ```
   You may pass option with path to config `--config=/custom/path/to/config`.
   Otherwise, it tries to use one of default paths to [config file](docs/config/global_config_php.md).

2. Commit updated files. You may config your pipeline/job on CI which commits updates.

### Docker

When using Docker, mount your project directory and run the container:

```shell
# Run with default config (searches for .todo-registrar.* files in project root)
docker run --rm -it \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest

# Run with explicit config path
docker run --rm -it \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest \
  --config=/code/.todo-registrar.yaml

# Run with verbose output to see processing details
docker run --rm -it \
  -v $(pwd):/code \
  ghcr.io/aeliot-tm/todo-registrar:latest \
  --config=/code/.todo-registrar.yaml \
  --verbose=very_verbose
```

**Available options:**
- `--config=/path/to/config` - Path to configuration file (inside container)
- `--verbose=very_verbose` or `-vv` - Enable very verbose output
- `--verbose=debug` or `-vvv` - Enable debug output
- `--quiet` or `-q` - Suppress all output except errors

## Integration on CI

The main idea is monitoring of new TODOs on single branch of repository to avoid creation of duplicated issues and
merge conflicts. The branch should be quite stable. At least, without development directly in it. And should be
near development as close as possible for earlier catching of tech-debt. Soon of all, it is called `development`.

So, you have to configure you integration depending on used git-server:

1. [GitLab pipelines](docs/gitlab/gitlab.md)
2. [GitHub Actions](docs/github/workflow.md)

## Configuration file

Configuration file can either in YAML format ([see documentation about config YAML-file](docs/config/global_config_yaml.md))
or PHP format ([see documentation about config PHP-file](docs/config/global_config_php.md)). You may define custom path
to config with option `--config=/custom/path/to/cofig`. When option `--config` is omitted then script tries to find
default config file in the root directory of project (exactly in directory from which the script was called).
It ties to find one of files:
1. `.todo-registrar.yaml`
2. `.todo-registrar.dist.yaml`
3. `.todo-registrar.php`
4. `.todo-registrar.dist.php`

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

| Issue Tracker                                   | Description                                                                                   |
|-------------------------------------------------|-----------------------------------------------------------------------------------------------|
| [Jira](https://www.atlassian.com/software/jira) | Supported via API tokens. See [description of configuration](docs/registrar/jira/config.md)   |
| Github issues                                   | Supported via API tokens. See [description of configuration](docs/registrar/github/config.md) |

## Articles

RU: https://habr.com/ru/articles/832994/
