# Issue Key Injection

Issue Key Injection is a global feature that controls how issue keys returned by registrars (e.g., `PROJ-123`, `#42`)
are injected back into TODO comments in source code. This feature is configurable and applies to all registrar types.

## How It Works

1. After creating an issue in the external tracker, the registrar returns an issue key
2. The key is injected into the original TODO comment based on configured position
3. File is saved with the updated comment

## Configuration

### Configuration Options (YAML)

| Option | Type | Default | Description |
|---|---|---|---|
| `position` | `string` | `after_separator` | Optional. Position where issue key is injected. |
| `newSeparator` | `string` | `null` | Optional. Separator to add if comment has no separator |
| `replaceSeparator` | `boolean` | `false` | Optional. Flag whether to replace existing separator with `newSeparator` or not |
| `summarySeparators` | `string[]` | `[':', '-', '>']` | Optional. List of recognized separators |

Example:
```yaml
issueKeyInjection:
  position: after_separator
  newSeparator: ':'
  replaceSeparator: false
  summarySeparators: [':', '-', '>']
```

### Position Options

| value | Description | Example |
|---|---|---|
| `after_separator` | Inject key after separator (default) | `TODO: PROJ-123 Fix bug` |
| `before_separator` | Inject key before separator (with space between issue key and separator) | `TODO PROJ-123 : Fix bug` |
| `before_separator_sticky` | Inject key before separator without space | `TODO PROJ-123: Fix bug` |

> **NOTE:** while the injection of created issue key it tries to allocate existing spaces.
> If there are several spaces between todo-tag and its summary it tries to take it into account
> to not add extra spaces and keep comment as compact as possible
>
> Before:
> ```php
> // TODO:  Summary of todo
> ```
>
> After (no spaces added):
> ```php
> // TODO: PROJ-123 Summary of todo
> ```

### Adding/Replacing of Separator

If a TODO comment has no separator and `newSeparator` is configured, it will be added.
And you can require replacing of existing separator to have more consistent comments format.
Set `replaceSeparator: true` to replace the existing separator with `newSeparator`.
