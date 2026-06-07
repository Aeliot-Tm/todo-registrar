# Dynamic Summary Prefix

Prepends a configurable prefix to the issue title (summary) before registration.

## What It Does

1. Reads `summaryPrefix` from `registrar.options.issue`
2. Replaces placeholders with values from the TODO comment
3. Concatenates the resolved prefix with `todo->getSummary()`

Applies to all registrars via `IssueSupporter::getSummary()`.

## Placeholders

| Placeholder | Resolved value |
|---|---|
| `{tag}` | Tag name as detected (uppercase stored internally, e.g. `TODO`) |
| `{tag_caps}` | Tag name in uppercase via `mb_strtoupper()` |
| `{assignee}` | First resolved assignee; empty string if none |

Placeholder names are case-insensitive. Multiple placeholders can appear in one prefix.

## Configuration

```yaml
registrar:
  options:
    issue:
      summaryPrefix: '[{tag_caps}] '
```

Example: tag `TODO`, summary `Fix bug` → title `[TODO] Fix bug`.

See [user documentation](../../../docs/dynamic_summary_prefix.md) for examples and details.

## Technical Details

Prefix resolution: `src/Service/Registrar/IssueSupporter.php` (`getSummaryPrefix()`, `getSummary()`).

Option defined in `src/Service/Registrar/AbstractGeneralIssueConfig.php`.
