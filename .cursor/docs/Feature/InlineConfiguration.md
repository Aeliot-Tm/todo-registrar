# Inline Configuration

Per-comment overrides parsed from `{EXTRAS: ...}` blocks inside TODO comment descriptions.

## What It Does

1. `ExtrasReader` finds the last `{EXTRAS: ...}` block in the comment description
2. Parses it with a JSON-like lexer (unquoted keys/values, quoted strings, arrays)
3. Wraps the result in `InlineConfigInterface` and attaches it to the `Todo` DTO
4. Registrar factories read inline values with higher priority than general config

Parse errors are logged to stderr; an empty inline config is used and processing continues.

## Syntax

```php
// TODO: Fix validation
//       {EXTRAS: {labels: [bug, urgent], assignee: john}}
```

```yaml
# TODO: Update defaults
#      {EXTRAS: {labels: [config], priority: high}}
```

Rules enforced by `ExtrasReader`:

- Block must match `{EXTRAS: ...}` (case-insensitive `EXTRAS`)
- Root object must contain exactly one key: `EXTRAS`
- Value of `EXTRAS` must be an object (not a list)

## Common Keys (All Registrars)

| Key | Description |
|---|---|
| `assignee` | User identifier (string or cast to array) |
| `assignees` | Same as `assignee` |
| `labels` | Labels/tags list |
| `showContext` | Context display format override |
| `contextTitle` | Context block title override |

Registrar-specific keys: see individual registrar docs (e.g. `linkedIssues` for JIRA, `milestone` for GitLab, `queue` for Yandex Tracker).

## Priority

For assignees (`IssueSupporter::getAssignees()`):

1. Tag assignee (`TODO@username`) — first in merge order
2. Inline `assignee` / `assignees`
3. General config `issue.assignee(s)`

For other fields, inline config typically overrides general config in each registrar's factory.

## Technical Details

| Class | Path |
|---|---|
| Reader | `src/Service/InlineConfig/ExtrasReader.php` |
| Lexer | `src/Service/InlineConfig/JsonLikeLexer.php` |
| Builder | `src/Service/InlineConfig/ArrayFromJsonLikeLexerBuilder.php` |
| Factory | `src/Service/InlineConfig/InlineConfigFactory.php` |
| Attachment | `src/Service/TodoBuilder.php` (`getInlineConfig()`) |
