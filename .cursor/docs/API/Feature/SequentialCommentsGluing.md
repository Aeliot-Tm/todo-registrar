# Sequential Comments Gluing

Merges consecutive single-line comments (`//` or `#`) into a single multi-line comment block for TODO processing.

See [user documentation](../../../../docs/sequential_comments_gluing.md) for configuration, rules, and examples.

## Configuration

Enable via `process.glueSequentialComments` option of general config. Default: `false` (disabled).

```yaml
process:
  glueSequentialComments: true
```

## Gluing Rules

### 1. Single-Line Comments Only

Only consecutive single-line comments are glued:
- PHP: `//` and `#` comments

Multi-line comments (`/* */` or `/** */`) are not affected and break the sequence.

### 2. Empty Line Breaks Sequence

Single line break between comments doesn't prevent gluing:

```php
// TODO: first line
//       second line
//       third line
```
All three comments are glued together.

But an **empty line** (multiple line breaks) breaks the sequence:

```php
// TODO: first line
//       second line

//       third line
```
Two separate groups: first two comments glued, third comment separate.

### 3. Indentation Ignored

Leading whitespace before the comment marker is ignored:

```php
// Comment 1
    // Comment 2 (different indentation)
        // Comment 3 (even more indentation)
```
All three comments are glued together.

## Multiple TODOs in Glued Block

```php
// TODO: Task 1
// TODO: Task 2 description
//       with more details
```

With gluing enabled:
- One composite comment node from all three lines
- Extractor finds two separate TODOs within that node
- Both TODOs get registered and keys injected correctly

## Technical Implementation

### Architecture

```
FileParser → ParsedFile (tokens + context)
     ↓
HeapRunner passes glueSequentialComments from config
     ↓
FileHeap.buildCommentNodes():
  Single pass through all tokens:
    - Filter comments
    - Group sequential single-line comments via CommentTokensGroup
    - Create CommentNode[] with MappedContext
```

### CommentTokensGroup

Groups consecutive single-line comment tokens. Manages pending whitespace between comments — single line breaks are buffered and included if the next token is another single-line comment; multiple line breaks flush the group.

### Key Properties

- Original line break style is preserved from the source file (no normalization)
- `CommentNode` receives all tokens from the group
- `MappedContext` provides context at the first token's line number

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `FileHeap` | Builds comment nodes from tokens, implements gluing logic |
| `CommentTokensGroup` | Groups consecutive single-line comment tokens |
| `CommentNode` | Wraps token(s) with context information |
| `ProcessConfig` | Holds `glueSequentialComments` setting |

### Source Paths

- Heap building: `src/Dto/FileHeap.php`
- Token grouping: `src/Dto/Token/CommentTokensGroup.php`
- Comment node: `src/Dto/Parsing/CommentNode.php`
- Config: `src/Dto/GeneralConfig/ProcessConfig.php`
