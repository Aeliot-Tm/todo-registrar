# Processing Flow

This page describes what happens from the moment you run the tool until source files and issue tracker are updated.

## Overview

```
Run command
    │
    ├─ Load configuration (.todo-registrar.yaml, .php, or YAML from STDIN)
    │
    └─ For each discovered file (PHP, YAML, or configured extensions)
            │
            ├─ Parse file and build comment list
            │       └─ optional: glue consecutive single-line comments
            │
            ├─ For each TODO/FIXME (and other configured tags)
            │       ├─ skip if comment already contains an issue key
            │       ├─ create issue in tracker (or reuse key when same-ticket gluing is on)
            │       ├─ inject issue key into comment
            │       └─ save source file immediately
            │
            └─ on first error: log and stop (fail-fast)
```

One file is held in memory at a time. After each successful registration the source file is written
to disk right away — not at the end of the file or the end of the run. See [When source files are saved](source_files_updating.md).

## Step 1: File discovery

The tool walks paths from configuration (`paths.in`, optional `append`, `exclude`, `extensions`, `name`, `sortByName`)
using Symfony Finder. By default, discovered files are sorted by path name (`sortByName: true`).

Default scanned extensions: `php`, `yaml`, `yml`. You can scan other extensions (for example `.module`)
via `paths.extensions` and map them to a parser with `process.extensionAliases`. See [General YAML config](config/general_config_yaml.md).

## Step 2: File parsing

Each file is parsed according to its extension:

| File type | Comment styles | Context in issues |
|---|---|---|
| PHP | `//`, `#`, `/* */`, `/** */` | Class, method, namespace, property, and more |
| YAML | `#` single-line comments | Document, mapping key, sequence item |

If no parser is registered for an extension, the file is skipped with an error message.

## Step 3: Comment grouping

Comments are collected from the parsed file. When [sequential comments gluing](sequential_comments_gluing.md) is enabled,
consecutive single-line comments (`//` or `#`) are treated as one multiline comment before TODO extraction.

## Step 4: TODO extraction

For each comment the tool:

1. Detects configured tags (default: `todo`, `fixme`; case-insensitive)
2. Parses optional assignee suffix (`TODO@username`)
3. Reads summary and multiline description (with indentation rules)
4. Parses optional inline config (`{EXTRAS: {...}}`)

See [Supported formats of comments](supported_patters_of_comments.md).

## Step 5: Skip already registered

If the tag line already contains a recognized issue key (for example `PROJ-123` or `#42`), the TODO is skipped.
This prevents duplicate issues on subsequent runs.

## Step 6: Register issue

The tool sends the TODO to the configured registrar (GitHub, GitLab, JIRA, Redmine, Yandex Tracker, or a custom implementation).

When [same-ticket gluing](same_ticket_gluing.md) is enabled, identical TODOs within the same run reuse
the first created key instead of calling the API again.

Issue title and description can include a [dynamic summary prefix](dynamic_summary_prefix.md),
[context path](context_display.md), and fields from [inline config](inline_config.md).

## Step 7: Inject key and save

The returned issue key is written into the comment according to [issue key injection](issue_key_injection.md)
settings. The source file is saved immediately.

When [dry-run mode](dry_run.md) is enabled (`--dry-run`), parsing and statistics collection run as usual, but the tool
uses the `DryRun` registrar instead of the configured tracker, does not call external APIs, and does not write
changes to source files. Use this to estimate how many TODOs would be registered in the scanned scope (for example,
changed files in a CI job). A config with `registrar.type: DryRun` simplifies CI setup when tracker credentials are
not needed. See [Processing report](report.md).

## Step 8: Report (optional)

When `--report-format` is set, run statistics are exported as JSON or YAML: run metadata (`meta`),
summary counters (`summary`), inserted issue keys with usage counts (`issues`), and per-file registration
counts (`files`). See [Processing report](report.md).

## Error handling

| Situation | Behavior |
|---|---|
| No parser for file extension | Error logged, file skipped, run continues |
| Parse error, registration failure, or other processing error | Error logged with file path, **run stops** (fail-fast) |
| Partial progress before failure | Keys already written and saved are **not rolled back** |

After fixing the cause of a failure, run the tool again — TODOs that already have keys are skipped.

## Related documentation

- [Features](features.md) — index of all capabilities
- [Configuration](configuration.md) — setup and registrar options
- [Integration on CI](integration_on_ci.md) — recommended workflow on a stable branch
