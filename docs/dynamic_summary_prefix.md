# Dynamic Summary Prefix

The `summaryPrefix` option supports dynamic placeholders that are replaced with actual values when creating issues.
This allows you to create more flexible and informative issue summaries based on TODO comment metadata.

## Supported Placeholders

### `{tag}`
Replaced with the tag name in its original case as it appears in the code.

**Example:**
```php
// TODO: Fix the bug
// FIXME: Refactor this method
```

With `summaryPrefix: '[{tag}] '`:
- `[TODO] Fix the bug`
- `[FIXME] Refactor this method`

### `{tag_caps}`
Replaced with the tag name converted to uppercase.

**Example:**
```php
// todo: Fix the bug
// fixme: Refactor this method
```

With `summaryPrefix: '{tag_caps}: '`:
- `TODO: Fix the bug`
- `FIXME: Refactor this method`

### `{assignee}`
Replaced with the first assignee from the list of assignees (from inline config, general config, or tag suffix).
If no assignee is specified, it will be replaced with an empty string.

**Example:**
```php
// TODO@john: Fix the bug
// FIXME{EXTRAS: {assignee: jane}}: Refactor this method
```

With `summaryPrefix: '[{assignee}] '`:
- `[john] Fix the bug`
- `[jane] Refactor this method`

## Configuration Examples

### YAML Configuration

```yaml
registrar:
  type: GitHub
  options:
    issue:
      summaryPrefix: '[{tag_caps}] '
```

### PHP Configuration

```php
$config->setRegistrar('GitHub', [
    'issue' => [
        'summaryPrefix' => '[{tag_caps}] ',
    ],
]);
```

## Multiple Placeholders

You can combine multiple placeholders in a single prefix:

```yaml
summaryPrefix: '{tag} by {assignee} - {tag_caps}: '
```

With a TODO comment:
```php
// fixme@bob: Refactor this method
```

Result: `fixme by bob - FIXME: Refactor this method`

## Case Insensitivity

All placeholders are case-insensitive, so these are equivalent:
- `{tag}`, `{TAG}`, `{Tag}`
- `{tag_caps}`, `{TAG_CAPS}`, `{Tag_Caps}`
- `{assignee}`, `{ASSIGNEE}`, `{Assignee}`

## Default Behavior

If `summaryPrefix` is not specified or is an empty string, no prefix will be added to the issue summary.
If `summaryPrefix` contains no placeholders, it will be used as-is (static prefix).

### Example with Static Prefix

```yaml
summaryPrefix: '[TODO] '
```

All issues will have the prefix `[TODO] ` regardless of the actual tag used in the code.

## When to Use

- **Standardized issue titles**: Use `{tag_caps}` to ensure consistent uppercase formatting
- **Developer tracking**: Use `{assignee}` to quickly identify who should handle the issue
- **Flexible formatting**: Use `{tag}` to preserve the original case from your codebase
- **Combined metadata**: Use multiple placeholders to create rich, informative issue summaries
