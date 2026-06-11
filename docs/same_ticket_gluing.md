# Same-Ticket Gluing

## Overview

When the same TODO text appears in several places (or several times in one file), this feature reuses
a single issue key for all matching comments within one run instead of creating duplicate tickets.

## Configuration

Enable in your [General YAML](config/general_config_yaml.md) or [General PHP](config/general_config_php.md) configuration.

Default value: `false` (disabled)

```yaml
process:
  glueSameTickets: true
```

## How It Works

1. The tool builds a normalized hash from tag, assignee, summary, and description (including the `{EXTRAS: ...}` block).
2. Before calling the issue tracker API, it checks whether the same hash was already registered in this run.
3. If a match is found and gluing is enabled, the existing issue key is reused and injected into the comment.
4. TODOs that already contain a recognized issue key in the tag line are always skipped, regardless of this setting.

## Identity Rules

Two TODOs are considered identical when these parts match after whitespace normalization:

- Tag name
- Assignee from the tag suffix (for example `TODO@john`)
- Summary (first line after the tag)
- Full description (remaining lines, including inline config)

Normalization collapses redundant whitespace (including line breaks) before comparison.

**Scope:** comparison applies within a single CLI run only. Cross-run deduplication is not supported.
Use injected issue keys manually to avoid duplicates on later runs.

## Example

```php
// TODO: Extract shared validation logic
```

```php
// TODO: Extract shared validation logic
```

With `glueSameTickets: true` in one run:

- The first occurrence creates issue `PROJ-42` (or `#42`, depending on the tracker).
- The second occurrence receives the same key without a second API call.

With `glueSameTickets: false` (default):

- Each occurrence creates a separate issue.

## Important Notes

- Works with all supported issue trackers
- Reused keys increase `summary.todos.glued` and appear in `issues` with `usageCounter`
  greater than `1` (or `1` when the key was created and reused in the same run)
- For detailed config reference, see [Option glueSameTickets](config/general_config_yaml.md#option-gluesametickets)
  in the general YAML config
