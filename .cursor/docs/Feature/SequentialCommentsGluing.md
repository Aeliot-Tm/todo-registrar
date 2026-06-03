# Sequential Comments Gluing

Merges consecutive single-line comments into one comment node before TODO extraction.

## What It Does

When `process.glueSequentialComments` is enabled, `FileHeap` groups adjacent single-line comment tokens separated by at most one blank line. The grouped tokens form one `CommentNode`; `Extractor` can then find multiple TODOs inside the combined text.

Applies to:

- PHP: `//` and `#` comments (`PhpTokenAdapter::isSingleLineComment()`)
- YAML: `#` comments (all YAML comments are single-line)

Multi-line block comments (`/* */`, `/** */`) flush any active group and are processed separately.

## Configuration

```yaml
process:
  glueSequentialComments: false   # default
```

## Gluing Rules

**Merged:**

```php
// TODO: first line
//       second line
//       third line
```

```yaml
# TODO: first
#       second
```

**Not merged** (empty line between comments):

```php
// TODO: first
//       second

//       third
```

**Not merged** (non-comment token with non-whitespace content breaks the group).

Indentation differences between consecutive comment lines do not prevent gluing.

## Multiple TODOs in One Glued Block

```php
// TODO: Task 1
// TODO: Task 2
//       details
```

One `CommentNode`, two `CommentPart` objects, two registrations (unless same-ticket gluing applies).

## Technical Details

| Class | Path |
|---|---|
| Grouping logic | `src/Dto/FileHeap.php` (`buildCommentNodes()`) |
| Token grouping | `src/Dto/Token/CommentTokensGroup.php` |
| Comment node | `src/Dto/Parsing/CommentNode.php` |
| Config | `src/Dto/GeneralConfig/ProcessConfig.php` |

Flow: `FileParserRegistry` → `ParsedFile` → `FileHeap` → `Extractor`.

See also: [Source File Parsing](SourceFileParsing.md).
