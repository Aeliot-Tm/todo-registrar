# Issue Key Injection

Controls how registrar-returned keys (`PROJ-123`, `#42`, `QUEUE-1`) are written back into source comments after successful registration.

## What It Does

1. After `registrar->register($todo)`, `HeapRunner` calls `$todo->injectKey($key)`
2. `CommentPart::injectKey()` modifies the first line of the comment at the configured position
3. `FileHeap` save callback writes the updated tokens to disk via `Saver`

Works for PHP and YAML source files (any file type that uses `TokenInterface`).

## Configuration

Top-level config key `issueKeyInjection`:

```yaml
issueKeyInjection:
  position: after_separator       # default
  newSeparator: null              # optional, exactly 1 character
  replaceSeparator: false         # default
  summarySeparators: [':', '-', '>']   # default
```

| Option | Default | Description |
|---|---|---|
| `position` | `after_separator` | Where to insert the key |
| `newSeparator` | `null` | Separator added when comment has none |
| `replaceSeparator` | `false` | Replace existing separator with `newSeparator` |
| `summarySeparators` | `:`, `-`, `>` | Separators recognized in tag line and tag detection |

`summarySeparators` also configure `Tag/Detector` — they affect both key injection and detection of existing keys on the tag line.

## Position Values

| Value | Example result |
|---|---|
| `after_separator` (default) | `TODO: PROJ-123 Fix bug` |
| `before_separator` | `TODO PROJ-123: Fix bug` |
| `before_separator_sticky` | `TODO PROJ-123: Fix bug` (separator stays after key) |

## Existing Keys (Skip Registration)

Comments with a recognized ticket key in the tag line are skipped. Detected formats include:

- JIRA / YouTrack: `PROJ-123`
- GitHub/GitLab number: `#123`
- GitHub URL or `owner/repo#123`
- Date `YYYY-MM-DD`, semver-like versions, composer constraints

## Technical Details

| Class | Path |
|---|---|
| Injection logic | `src/Dto/Comment/CommentPart.php` (`injectKey()`) |
| Position enum | `src/Enum/IssueKeyPosition.php` |
| Config DTO | `src/Dto/GeneralConfig/IssueKeyInjectionConfig.php` |
| YAML validation | `src/Dto/GeneralConfig/IssueKeyInjectionArrayConfig.php` |
| Tag detection | `src/Service/Tag/Detector.php` |
| Builder wiring | `src/Service/TodoBuilder.php`, `TodoBuilderFactory.php` |

Note: `issueKeyInjection.issueKeyPosition` is listed as a known YAML key for validation but is not read; use `position`.
