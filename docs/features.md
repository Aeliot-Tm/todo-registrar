# Features

TODO Registrar scans PHP and YAML source files for configured tags (for example `TODO`, `FIXME`), creates issues in an external tracker, and writes issue keys back into comments. This page lists all documented capabilities and links to detailed guides.

For the end-to-end algorithm, see [Processing Flow](processing_flow.md).

## Comment detection and parsing

| Feature | Description |
|---|---|
| [Supported formats of comments](supported_patters_of_comments.md) | Tag syntax, assignee suffix, multiline descriptions, PHP and YAML comment types |
| [Sequential comments gluing](sequential_comments_gluing.md) | Treat consecutive `//` or `#` lines as one multiline TODO |
| [Inline configuration](inline_config.md) | Per-TODO `{EXTRAS: {...}}` block for labels, assignees, linked issues, and more |

## Issue creation and metadata

| Feature | Description |
|---|---|
| [Allowed labels](allowed_labels.md) | Restrict which labels/tags are applied to created issues |
| [Context display](context_display.md) | Include code or YAML structure path in the issue description |
| [Dynamic summary prefix](dynamic_summary_prefix.md) | Placeholders `{tag}`, `{tag_caps}`, `{assignee}` in issue titles |
| [JIRA linked issues](registrar/JIRA/linked_issues.md) | Auto-link new JIRA issues to existing tickets from inline config |

## Keys, files, and run behavior

| Feature | Description |
|---|---|
| [Issue key injection](issue_key_injection.md) | Where and how issue keys are written into comments |
| [Same-ticket gluing](same_ticket_gluing.md) | Reuse one ticket for identical TODOs within a single run |
| [When source files are saved](source_files_updating.md) | Incremental save after each registration, fail-fast on errors |
| [Dry-run mode](dry_run.md) | Count TODOs without API calls or file changes; optional `DryRun` registrar in config |
| [Processing report](report.md) | Export run statistics as JSON or YAML |

## Configuration and integration

| Feature | Description |
|---|---|
| [Configuration](configuration.md) | General config, registrars, environment variables |
| [Command line options](command_line_options.md) | `--config`, report options, verbosity |
| [Integration on CI](integration_on_ci.md) | Running on a stable branch to avoid duplicate issues |
| [Customization](customization.md) | Custom registrars, inline config readers, finders |

## Supported issue trackers

| Tracker | Configuration |
|---|---|
| [GitHub](registrar/GitHub/config.md) | Issues API via personal access token |
| [GitLab](registrar/GitLab/config.md) | Issues API via HTTP token or OAuth |
| [JIRA](registrar/JIRA/config.md) | Issues API via personal access token |
| [Redmine](registrar/Redmine/config.md) | Issues API via API key or Basic Auth |
| [Yandex Tracker](registrar/YandexTracker/config.md) | Issues API via OAuth token |
