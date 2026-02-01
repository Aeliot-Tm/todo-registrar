# Issue Key Injection

Overview is in user's documentation: [issue key injection](../../../../docs/issue_key_injection.md)

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

### Enum Values

`IssueKeyPosition` enum contains three values:

| Value | Constant | Description |
|---|---|---|
| `after_separator` | `AFTER_SEPARATOR` | Inject after separator |
| `before_separator` | `BEFORE_SEPARATOR` | Inject before separator with space |
| `before_separator_sticky` | `BEFORE_SEPARATOR_STICKY` | Inject before separator without space |

### Key Injection Algorithm

Located in `CommentPart::injectKey()`:

1. Find separator offset in comment text
2. Calculate injection offset based on position:
   - `after_separator`: offset = separator offset + 1
   - `before_separator`: offset = separator offset
   - `before_separator_sticky`: offset = separator offset
3. Insert key at calculated offset with proper spacing
4. Add or replace separator if configured

### Configuration Priority

Two fields describing position are available:
- `position` (recommended)
- `issueKeyPosition` (deprecated, added for compatibility with interface)

The `position` field has priority over the deprecated `issueKeyPosition` field:

## Default Values

If `issueKeyInjection` is not configured, the following defaults are used:
- `position`: `after_separator`
- `newSeparator`: `null` (no separator is added)
- `replaceSeparator`: `false`
- `summarySeparators`: `[':', '-', '>']`
