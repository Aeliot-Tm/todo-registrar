# Sequential Comments Gluing

Merges consecutive single-line comments into one comment node before TODO extraction.

## What It Does

When `process.glueSequentialComments` is enabled, `FileHeap` groups adjacent single-line comment tokens separated by at most one blank line. Format-specific glue gates decide whether the current stream token may join the active group (with lookahead for whitespace between comments). The grouped tokens form one `CommentNode`; `Extractor` can then find multiple TODOs inside the combined text.

Applies to:

- PHP: `//` and `#` single-line comments
- YAML: `#` comments; newline and indent tokens between consecutive `#` lines are glueable whitespace

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
| Grouping logic | `src/Service/Comment/CommentNodesBuilder.php` |
| Token stream | `src/Dto/Token/TokenStream.php`; `ParsedFile::getTokenStream()` |
| Glue gates | `src/Service/Comment/SequentialCommentGlueGate/PhpSequentialCommentGlueGate.php`, `YamlSequentialCommentGlueGate.php` |
| Gate registry | `src/Service/Comment/SequentialCommentGlueGateRegistry.php` |
| Token grouping | `src/Dto/Token/CommentTokensGroup.php` |
| Comment node | `src/Dto/Parsing/CommentNode.php` |
| Config | `src/Dto/GeneralConfig/ProcessConfig.php` |

Flow: `FileParserRegistry` → `ParsedFile` → `SequentialCommentGlueGateRegistry` + `CommentNodesBuilder` → `Extractor`.

See also: [Source File Parsing](SourceFileParsing.md).
