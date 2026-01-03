![logo](docs/logo.svg)

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=black)](https://packagist.org/packages/aeliot/todo-registrar)
[![WFS](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated_testing.yml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated_testing.yml?query=branch%3Amain)
[![Security Audit](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/security-audit.yaml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/security-audit.yaml?query=branch%3Amain)
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

There are several ways of installation:
1. [GitHub Action](https://github.com/marketplace/actions/todo-registrar)
2. [Docker](#installation-with-docker)
3. [Downloading of PHAR directly](#downloading-of-phar-directly)
4. [PHIVE](#installation-with-phive)
5. [Composer](#installation-with-composer)

First of all, I recommend Docker container case it provides fully isolated solution.
It is no matter which version of PHP installed in yous system and which components required by Composer (no dependency hell).

The next one is using of single `PHAR` file. It frees you from dependency hell,
but you have to pay attention to version of PHP installed in you system and its modules.
However, this may be more familiar to you. You can [automate it by using of PHIVE](#installation-with-phive)
or [downloading do it manually](#downloading-of-phar-directly).

The last one is [installing by Composer](#composer). The most common, but less flexible method case may lead to the dependency hell.

### Installation with Docker

You can use the pre-built Docker image from GitHub Container Registry:

```shell
# Pull the latest image
docker pull ghcr.io/aeliot-tm/todo-registrar:latest

# Or use a specific version tag
docker pull ghcr.io/aeliot-tm/todo-registrar:v1.8.0
```

### Downloading of PHAR directly

Download PHAR directly to root directory:
```shell
# Do adjust the URL if you need a release other than the latest
wget -O todo-registrar.phar "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar"

chmod +x todo-registrar.phar
```

Additional instructions read [here](docs/installation/phar_directly.md)

### Installation with PHIVE

Basically, it's enough to call command (with installed `phive`):
```shell
phive install todo-registrar
```
Additional instructions read [here](docs/installation/phive.md).

### Installation with Composer

Require the package as development dependency with [Composer](https://getcomposer.org/doc/03-cli.md#install-i):
```shell
composer require --dev aeliot/todo-registrar
```

## Using

1. Create [configuration file](docs/config/global_config.md)
2. Call shell script [in command line](#command-line).
3. Commit updated files. You may config your pipeline/job on CI which commits updates.

### Command Line

First of all, pay attention to **available options:**

| Long Form | Short From | Description |
|---|---|---|
| `--config=/path/to/config` | `-c /path/to/config` | Path to [configuration file](docs/config/global_config.md) when it is not in default place |
| | `-q`, `-v`, `-vv`, `-vvv` | Verbosity levels. The command uses [Symfony Console verbosity levels](https://symfony.com/doc/7.4/console/verbosity.html) |

**NOTE:** You may pass a special value`--config=STDIN` then [script obtains YAML from STDIN](docs/config/global_config_yaml.md#loading-from-stdin).

#### Using with Docker

To analyze your project, mount your code directory and configuration file, and run the container.

a. Basic usage with default config (searches for .todo-registrar.* files in project root)
   ```shell
   docker run --rm -it \
     -v $(pwd):/code \
     ghcr.io/aeliot-tm/todo-registrar:latest
   ```

b. With custom config file path (relative or absolute)
   ```shell
   docker run --rm -it \
     -v $(pwd):/code \
     ghcr.io/aeliot-tm/todo-registrar:latest \
     --config=ci/code_quality/.todo-registrar.yaml
   ```

c. With verbose output to see processing details
   ```shell
   docker run --rm -it \
     -v $(pwd):/code \
     ghcr.io/aeliot-tm/todo-registrar:latest \
     --config=/code/.todo-registrar.yaml \
     -vv
   ```

**Important notes:**
- Mount your project directory to `/code` (this is the working directory inside the container)
- The config file can be inside your project directory (will be found automatically) or mounted separately
- Use `-it` flags for interactive mode if you need to see real-time output
- The container uses unbuffered output, so messages will appear in real-time

#### Using of PHAR file

Call script similarly to [Docker](#using-with-docker) but without the mounting of the code.

```shell
php todo-registrar.phar <options>
```

#### Using of Installed by Composer

Call script as usual:

```shell
vendor/bin/todo-registrar <options>
```

### Integration on CI

The main idea is monitoring of new TODOs on single branch of repository to avoid creation of duplicated issues and
merge conflicts. The branch should be quite stable. At least, without development directly in it. And should be
near development as close as possible for earlier catching of tech-debt. Soon of all, it is called `development`.

So, you have to configure you integration depending on used git-server:

1. [GitLab CI](docs/GitlLab/integration_on_ci.md)
2. [GitHub Actions](docs/GitHub/workflow.md)

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
| GitHub issues                                   | Supported via API tokens. See [description of configuration](docs/registrar/GitHub/config.md) |
| [GitLab](https://about.gitlab.com/)             | Supported via API tokens (HTTP Token or OAuth). See [description of configuration](docs/registrar/GitLab/config.md) |
| [JIRA](https://www.atlassian.com/software/jira) | Supported via API tokens. See [description of configuration](docs/registrar/JIRA/config.md)   |
| [Redmine](https://www.redmine.org/)            | Supported via API keys or Basic Auth. See [description of configuration](docs/registrar/Redmine/config.md) |
| [Yandex Tracker](https://tracker.yandex.com/)   | Supported via OAuth tokens. See [description of configuration](docs/registrar/YandexTracker/config.md) |

## Articles

RU: https://habr.com/ru/articles/832994/
