![logo](https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/logo.svg)

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=3c3d41)](https://packagist.org/packages/aeliot/todo-registrar)
[![Testing](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated-testing.yaml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated-testing.yaml?query=branch%3Amain)
[![Security Audit](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/security-audit.yaml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/security-audit.yaml?query=branch%3Amain)
[![GitHub License](https://img.shields.io/github/license/Aeliot-Tm/todo-registrar?label=License&labelColor=3c3d41)](LICENSE)

It scans PHP and YAML source files for TODO/FIXME and other configured tags,
registers them as issues in trackers such as JIRA or GitHub, and injects issue keys back
into comments — with labels, linked issues, assignees, and other metadata as you configure.

## Motivation

Developers often leave notes in code so they do not forget to fix something — and then forget anyway.
One reason is that such comments are hard to manage centrally.

Why write a comment in code instead of creating an issue? It is convenient: no issue tracker UI,
no repeated form filling, and the note sits exactly where the change is needed.
Whatever the reason, those comments can stay in the codebase for years.

Someone has to manage them.

This tool registers issues with the parameters you need and injects ticket IDs/keys into
the original comments. That prevents duplicate issues and makes it easy to jump
from a tracker ticket to the right place in code.

## How it works

1. Detect a TODO (or other configured tag) in a comment.
2. Create an issue in the issue tracker ([list of supported](#supported-issue-trackers)).
3. Inject the ticket key into the comment and save the source file.

![detect_register_inject.png](https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/detect_register_inject.png)

> See latest **benchmark [here](https://github.com/Aeliot-Tm/todo-registrar-benchmark/blob/main/benchmark.md)**.

## Using

**Basic usage:**
1. Create a [configuration file](docs/config/general_config.md).
2. Run the CLI with the needed [command line options](docs/command_line_options.md).
3. Commit updated files. You can configure a CI job to run the tool and commit changes automatically.

**Prepared ways to run:**
1. [TODO Registrar Action](https://github.com/marketplace/actions/todo-registrar) for GitHub repositories.
2. [Docker container](docs/using/docker.md).
3. [PHAR file](docs/using/phar.md).
4. [Composer package](docs/using/composer.md).

For GitHub-hosted repositories, the GitHub Action is the simplest option.

Docker provides a fully isolated environment — no dependency on your local PHP version or Composer packages.

A single `PHAR` file also avoids dependency conflicts, but you must match PHP version and extensions on the host.

The Composer package is the most common integration, but less flexible than Docker or PHAR.

### Documentation references

1. [Processing flow](docs/processing_flow.md) — what happens on each run
2. [Features](docs/features.md) — index of all capabilities
3. [Command line options](docs/command_line_options.md)
4. [Configuration](docs/configuration.md):
   1. [General config](docs/config/general_config.md)
      1. Config format:
         1. [YAML file](docs/config/general_config_yaml.md)
         2. [PHP file](docs/config/general_config_php.md)
         3. [YAML from STDIN](docs/config/general_config_yaml.md#loading-from-stdin)
   2. [Inline config](docs/inline_config.md)
   3. Issue trackers:
       1. [GitHub](docs/registrar/GitHub/config.md)
       2. [GitLab](docs/registrar/GitLab/config.md)
       3. [JIRA](docs/registrar/JIRA/config.md)
       4. [Redmine](docs/registrar/Redmine/config.md)
       5. [Yandex Tracker](docs/registrar/YandexTracker/config.md)
5. [Supported formats of comments](docs/supported_patters_of_comments.md)
6. [Integration on CI](docs/integration_on_ci.md)
7. [Customization](docs/customization.md)

## Supported Issue Trackers

| Issue Tracker | Description |
|---|---|
| <img src="https://cdn.simpleicons.org/github" width="24" height="24" alt="" /> [GitHub](https://github.com/) | See [description of configuration](docs/registrar/GitHub/config.md). Supported via API tokens. |
| <img src="https://cdn.simpleicons.org/gitlab" width="24" height="24" alt="" /> [GitLab](https://about.gitlab.com/) | See [description of configuration](docs/registrar/GitLab/config.md). Supported via API tokens (HTTP Token or OAuth). |
| <img src="https://cdn.simpleicons.org/jira" width="24" height="24" alt="" /> [JIRA](https://www.atlassian.com/software/jira) | See [description of configuration](docs/registrar/JIRA/config.md). Supported via API tokens. |
| <img src="https://cdn.simpleicons.org/redmine" width="24" height="24" alt="" /> [Redmine](https://www.redmine.org/) | See [description of configuration](docs/registrar/Redmine/config.md). Supported via API keys or Basic Auth. |
| <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Logo_Yandex_Tracker_2021.svg" width="24" height="24" alt="" /> [Yandex Tracker](https://tracker.yandex.com/) | See [description of configuration](docs/registrar/YandexTracker/config.md). Supported via OAuth tokens. |
| Other Issue Trackers | Request an implementation through the [issues](https://github.com/Aeliot-Tm/todo-registrar/issues) section or see [customization](docs/customization.md) section to do it yourself. |

## Articles

RU: https://habr.com/ru/articles/832994/

## Contributing

Read [contributing instructions](CONTRIBUTING.md).
