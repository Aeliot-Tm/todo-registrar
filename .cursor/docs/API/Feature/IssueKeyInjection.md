# Issue Key Injection

Controls how issue keys returned by registrars (e.g. `PROJ-123`, `#42`) are injected back into TODO comments in source code after issue creation.

See [user documentation](../../../../docs/issue_key_injection.md) for configuration examples and edge cases.

## Configuration Options

| Option | Type | Default | Description |
|---|---|---|---|
| `position` | `string` | `after_separator` | Where the issue key is placed |
| `newSeparator` | `string` | `null` | Separator to add if the comment has none |
| `replaceSeparator` | `boolean` | `false` | Whether to replace existing separator with `newSeparator` |
| `summarySeparators` | `string[]` | `[':', '-', '>']` | Recognized separators between tag and summary |

## Position Options

| Value | Constant | Example |
|---|---|---|
| `after_separator` (default) | `AFTER_SEPARATOR` | `TODO: PROJ-123 Fix bug` |
| `before_separator` | `BEFORE_SEPARATOR` | `TODO PROJ-123 : Fix bug` |
| `before_separator_sticky` | `BEFORE_SEPARATOR_STICKY` | `TODO PROJ-123: Fix bug` |

## Key Injection Algorithm

Located in `CommentPart::injectKey()`:

1. Find separator offset in comment text
2. Calculate injection offset based on position:
   - `after_separator`: offset = separator offset + 1
   - `before_separator`: offset = separator offset
   - `before_separator_sticky`: offset = separator offset
3. Insert key at calculated offset with proper spacing
4. Add or replace separator if configured

Existing spaces are respected to avoid adding extra whitespace.

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `IssueKeyInjectionConfig` | Holds parsed injection configuration |
| `IssueKeyInjectionArrayConfig` | Validates YAML configuration |
| `IssueKeyPosition` | Enum with position values |
| `TodoBuilder` | Creates Todo with injection config |
| `TodoBuilderFactory` | Builds TodoBuilder with config |
| `CommentPart` | Performs actual key injection |

### Source Paths

- Config DTO: `src/Dto/GeneralConfig/IssueKeyInjectionConfig.php`
- Array config: `src/Dto/GeneralConfig/IssueKeyInjectionArrayConfig.php`
- Position enum: `src/Enum/IssueKeyPosition.php`
- Injection logic: `src/Dto/Comment/CommentPart.php`
- Builder: `src/Service/TodoBuilder.php`
