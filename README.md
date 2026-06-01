![logo](https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/logo.svg)

[![GitHub Release](https://img.shields.io/github/v/release/Aeliot-Tm/todo-registrar?label=Release&labelColor=black)](https://packagist.org/packages/aeliot/todo-registrar)
[![Testing](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated-testing.yaml/badge.svg?branch=main)](https://github.com/Aeliot-Tm/todo-registrar/actions/workflows/automated-testing.yaml?query=branch%3Amain)
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
you can use all power of management to plan solving of lacks of your code.

This script do it for you. It registers issues with all necessary params. Then injects IDs/Keys of created issues
into comment in code. This prevents creating of issues twice and injected marks helps to find proper places in code quickly.

## How it works

1. Detect TODO in comment.
2. Create issue in issue tracker ([list of supported](#supported-issue-trackers)).
3. Inject number of ticket into TODO-comment.

![detect_register_inject.png](https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/detect_register_inject.png)

> See latest **benchmark [here](https://github.com/Aeliot-Tm/todo-registrar-benchmark/blob/main/benchmark.md)**.

## Using

**Basic using:**
1. Create [configuration file](docs/config/general_config.md).
2. Call shell script with necessary [command line options](docs/command_line_options.md).
3. Commit updated files. You can config your pipeline/job on CI which commits updates.

**There are prepared several ways of using:**
1. Using of GitHub Action: [TODO Registrar Action](https://github.com/marketplace/actions/todo-registrar).
2. [Using of Docker Container](docs/using/docker.md).
3. [Using of PHAR file](docs/using/phar.md).
4. [Using of Composer Package](docs/using/composer.md).

I recommend [TODO Registrar Action](https://github.com/marketplace/actions/todo-registrar) for repositories hosted on GitHub.

The next recommendation is using of Docker container. It provides fully isolated solution.
There is no matter which version of PHP installed in yous system
and which components required by Composer (no dependency hell).

The next one is using of single `PHAR` file. It frees you from dependency hell,
but you have to pay attention to version of PHP installed in you system and its modules.
However, this may be more familiar for you.

The last one is using of Composer package. The most common, but less flexible method.

### Documentation references

1. [Command Line Options](docs/command_line_options.md)
2. [Configuration](docs/configuration.md):
   1. [General Config](docs/config/general_config.md)
      1. Config format:
         1. [YAML file](docs/config/general_config_yaml.md)
         2. [PHP file](docs/config/general_config_php.md)
         3. [YAML from STDIN](docs/config/general_config_yaml.md#loading-from-stdin)
   2. [Inline Config](docs/inline_config.md)
   3. Specific for issue trackers supported out of the box:
       1. [GitHub](docs/registrar/GitHub/config.md)
       2. [GitLab](docs/registrar/GitLab/config.md)
       3. [JIRA](docs/registrar/JIRA/config.md)
       4. [Redmine](docs/registrar/Redmine/config.md)
       5. [Yandex Tracker](docs/registrar/YandexTracker/config.md)
3. [Supported formats of comments](docs/supported_patters_of_comments.md)
4. [Integration on CI](docs/integration_on_ci.md)
5. [Customization](docs/customization.md)

## Supported Issue Trackers

| Issue Tracker | Description |
|---|---|
| <img src="https://cdn.simpleicons.org/github" width="24" height="24" alt="" /> [GitHub](https://github.com/) | See [description of configuration](docs/registrar/GitHub/config.md). Supported via API tokens. |
| <img src="https://cdn.simpleicons.org/gitlab" width="24" height="24" alt="" /> [GitLab](https://about.gitlab.com/) | See [description of configuration](docs/registrar/GitLab/config.md). Supported via API tokens (HTTP Token or OAuth). |
| <img src="https://cdn.simpleicons.org/jira" width="24" height="24" alt="" /> [JIRA](https://www.atlassian.com/software/jira) | See [description of configuration](docs/registrar/JIRA/config.md). Supported via API tokens. |
| <img src="https://cdn.simpleicons.org/redmine" width="24" height="24" alt="" /> [Redmine](https://www.redmine.org/) | See [description of configuration](docs/registrar/Redmine/config.md). Supported via API keys or Basic Auth. |
| <img src="https://upload.wikimedia.org/wikipedia/commons/f/f3/Logo_Yandex_Tracker_2021.svg" width="24" height="24" alt="" /> [Yandex Tracker](https://tracker.yandex.com/) | See [description of configuration](docs/registrar/YandexTracker/config.md). Supported via OAuth tokens. |
| Any custom Issue Tracker | Read about [customization](docs/customization.md) |

## Articles

RU: https://habr.com/ru/articles/832994/

## Contributing

Read [contributing instructions](CONTRIBUTING.md).
