# Sequential Comments Gluing

## Overview

Sequential Comments Gluing is a feature that allows multiple consecutive single-line comments to be treated as a single multi-line comment for TODO processing. This is useful for languages where multi-line comments are not commonly used (like YAML) or when developers prefer using consecutive single-line comments in PHP.

## Configuration

Enable this feature in your `.todo-registrar.yaml` configuration:

```yaml
process:
  glueSequentialComments: true

registrar:
  type: github
  # ... other config
```

Default value: `false` (disabled)

## Gluing Rules

### 1. Single-Line Comments Only

Only consecutive single-line comments are glued:
- PHP: `//` and `#` comments

Multi-line comments (`/* */` or `/** */`) are not affected.

### 2. Single Empty Line Allowed, Multiple Break Sequence

Single line break (one `\n` or `\r\n`) between comments doesn't prevent gluing:

```php
// TODO: first line
//       second line
//       third line
```
✅ **All three comments glued together**

But **multiple line breaks** (empty line) break the sequence:

```php
// TODO: first line
//       second line

//       third line
```
❌ **Two separate groups**: first two comments glued, third comment separate

### 3. Indentation Ignored

Leading whitespace before the comment marker is ignored:

```php
// Comment 1
    // Comment 2 (different indentation)
        // Comment 3 (even more indentation)
```
✅ **All three comments glued together**

## Examples

### Example 1: Multi-Line TODO in PHP

**Without gluing:**
```php
// TODO: Implement user authentication
```

Only the first line is processed.

**With gluing enabled:**
```php
// TODO: Implement user authentication
//       - Add login form
//       - Validate credentials
//       - Create session
```

All four lines are processed as a single TODO comment.

### Example 2: YAML Configuration Comments

**YAML file:**
```yaml
# TODO: Add validation for email field
#       Should check format and domain
#       Display error message on invalid input
email: user@example.com
```

With gluing enabled, all three comment lines are treated as one TODO.

### Example 3: Multiple TODOs

**Source:**
```php
// TODO: Task 1
// TODO: Task 2 description
//       with more details
```

**Result:**
- If glued: ONE composite token containing all three lines
- Extractor finds TWO TODOs within that token
- Both TODOs get registered and keys injected correctly

### Example 4: Mixed Comments

**Source:**
```php
// TODO: Single-line task

/* Multi-line comment
   not affected by gluing */

// TODO: Another task
//       with description
```

**Result:**
- First `//` comment: processed normally
- `/* */` comment: not affected by gluing
- Last two `//` comments: glued together (if enabled)

## Technical Implementation

### Architecture

```
FileParser → ParsedFile (tokens + context)
     ↓
HeapRunner extracts shouldGlue from config
     ↓
FileHeap.buildCommentNodes():
  Single pass through all tokens:
    - Filter comments
    - Glue sequential single-line comments if enabled
    - Create CommentNode[] with LazyContextMap
```

**Performance Optimization:**
- Only one iteration through all tokens (no multiple loops, no nested iterations)
- `TokenInterface::isSingleLineComment()` determines comment type (format-specific)
- Group breaks on:
  - Non-empty, non-comment tokens
  - Multiple line breaks (empty line between comments)

### CompositeToken

When comments are glued, a `CompositeToken` is created that wraps multiple token objects, **preserving original line break characters** (`\n`, `\r\n`, or mixed):

```php
// Original tokens in file (PHP tokenizer separates comments and whitespace)
$tokens = [
    PhpTokenAdapter("// TODO: line 1"),    // Line 5 - T_COMMENT
    PhpTokenAdapter("\n"),                 // Line 5 - T_WHITESPACE
    PhpTokenAdapter("// line 2"),          // Line 6 - T_COMMENT
    PhpTokenAdapter("\r\n"),               // Line 6 - T_WHITESPACE (Windows line ending preserved)
    PhpTokenAdapter("// line 3"),          // Line 7 - T_COMMENT
];

// CompositeToken wraps them (same object references!)
$composite = new CompositeToken($tokens);

// When key is injected, setText() is called
$composite->setText("// TODO: KEY-123 line 1\n// line 2\r\n// line 3");

// Result:
// $tokens[0]->getText() = "// TODO: KEY-123 line 1\n// line 2\r\n// line 3"
// $tokens[1]->getText() = ""
// $tokens[2]->getText() = ""
// $tokens[3]->getText() = ""
// $tokens[4]->getText() = ""

// When Saver concatenates all tokens, no duplication occurs!
```

**Important:** The original line break style is preserved from the source file. The system does not normalize line endings.

### Key Properties

- **Line Numbers Preserved:** `CompositeToken::getLine()` returns the line number of the first token in the group
- **Token ID Preserved:** `CompositeToken::getId()` returns the ID of the first token
- **Comment Detection:** `CompositeToken::isComment()` delegates to the first token
- **No Duplication:** `setText()` clears all tokens except the first to prevent duplication during file save

## Use Cases

1. **YAML Files:** All comments in YAML are single-line (`#`), making gluing essential for multi-line TODOs
2. **Code Style Preference:** Teams that prefer `//` over `/* */` for consistency
3. **Legacy Code:** Codebases with established patterns of multi-line `//` comments
4. **Shell Scripts:** Similar to YAML, all comments use `#`

## Limitations

1. **Performance:** Minimal overhead for checking consecutive comments
2. **Comment Style:** Only affects single-line comments; multi-line comments work as before
3. **Language Support:** Currently implemented for PHP; extensible to other languages through TokenInterface

## See Also

- [Architecture Overview](../Architecture/Overview.md)
- [Processing Flow](../Architecture/ProcessingFlow.md) - See Step 2.5 for detailed gluing algorithm
