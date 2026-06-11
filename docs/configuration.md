# Configuration

Configuration controls which files are scanned, which issue tracker is used, and how TODO comments are processed.

Start with the [general config file](config/general_config.md) (YAML or PHP).
For a full list of capabilities, see [Features](features.md).

## General config

1. [General config file](config/general_config.md) — File discovery, tags, registrar type, processing options.
   1. [YAML format](config/general_config_yaml.md) — Full YAML reference, environment variables, STDIN loading.
   2. [PHP format](config/general_config_php.md) — Programmatic configuration.

## Per-issue and comment options

1. [Inline configuration](inline_config.md) — `{EXTRAS: {...}}` in TODO comments.
2. [Supported formats of comments](supported_patters_of_comments.md) — Tag syntax, assignee, multiline text.
3. [Same-ticket gluing](same_ticket_gluing.md) — One ticket for identical TODOs in one run.
4. [Sequential comments gluing](sequential_comments_gluing.md) — Glue consecutive `//` or `#` lines.
5. [Issue key injection](issue_key_injection.md) — Position and separators for injected keys.

## Registrar options

Each built-in tracker has its own configuration and inline config keys:

1. [GitHub](registrar/GitHub/config.md)
2. [GitLab](registrar/GitLab/config.md)
3. [JIRA](registrar/JIRA/config.md) (see also [JIRA linked issues](registrar/JIRA/linked_issues.md))
4. [Redmine](registrar/Redmine/config.md)
5. [Yandex Tracker](registrar/YandexTracker/config.md)

For dry-run and CI statistics without tracker credentials, see [Dry-run mode](dry_run.md) (`registrar.type: DryRun`).

## Shared registrar features:

1. [Allowed labels](allowed_labels.md) — Filter labels applied to issues.
2. [Context display](context_display.md) — Code/YAML path in issue description.
3. [Dynamic summary prefix](dynamic_summary_prefix.md) — `{tag}`, `{assignee}` in issue titles.

## Custom integrations

[Customization](customization.md) describes how to add a custom registrar, inline config reader, or file finder.
