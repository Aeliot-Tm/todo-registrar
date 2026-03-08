# Inline Configuration

Enables per-comment configuration of created issues via `{EXTRAS: ...}` blocks within TODO comments.

## What It Does

1. Parses `{EXTRAS: {key: value}}` blocks from TODO comment descriptions
2. Merges inline values with general config (inline takes priority)
3. Passes the merged configuration to the registrar for issue creation

## Syntax

Uses a format similar to JS objects / JSON without quotes. Supports multi-line blocks, quoted keys/values for multi-word strings, and JSON-compliant escape sequences.

## Common Keys (all registrars)

| Key | Description |
|---|---|
| `assignee` | User identifier to assign to the issue |
| `assignees` | Same as `assignee` |
| `contextTitle` | Override context path title |
| `labels` | Labels/tags for the issue |
| `showContext` | Override context display format |

Each registrar also supports tracker-specific keys (e.g. `linkedIssues` for JIRA, `milestone` for GitLab).

## Config Priority

1. Tag assignee (`TODO@john`) — highest
2. Inline config (`{EXTRAS: ...}`)
3. General config — lowest

See [user documentation](../../../docs/inline_config.md) for syntax details, examples, and registrar-specific keys.

## Key Source Paths

- Extras reader: `src/Service/InlineConfig/ExtrasReader.php`
- Inline config directory: `src/Service/InlineConfig/`
