# Dynamic Summary Prefix

Adds a configurable prefix to issue titles with support for dynamic placeholders that are resolved from TODO comment metadata.

## What It Does

1. Takes the `summaryPrefix` value from the registrar's `issue` config
2. Replaces placeholders with actual values from the TODO comment
3. Prepends the resolved prefix to the issue summary

## Supported Placeholders

| Placeholder | Description |
|---|---|
| `{tag}` | Tag name in original case (e.g. `TODO`, `fixme`) |
| `{tag_caps}` | Tag name in uppercase (e.g. `TODO`, `FIXME`) |
| `{assignee}` | First assignee name; empty string if none |

Placeholders are case-insensitive. Multiple placeholders can be combined.

## Configuration

Configured via `summaryPrefix` option in the registrar's `issue` section.

See [user documentation](../../../docs/dynamic_summary_prefix.md) for examples and details.

## Key Source Paths

- Prefix resolution: `src/Service/Registrar/IssueSupporter.php`
