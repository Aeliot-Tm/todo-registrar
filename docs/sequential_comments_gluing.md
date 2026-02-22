# Sequential Comments Gluing

## Overview

This feature allows consecutive single-line comments to be treated as a single multi-line comment for TODO processing.

## Configuration

Enable in your [General YAML](config/general_config_yaml.md) or [General PHP](config/general_config_php.md) configuration.

Default value: `false` (disabled)

## How It Works

For example, there are several consecutive single-line comments:
```php
// TODO: Implement user authentication
//       - Add login form
//       - Validate credentials
```
1. Without Gluing (default) only the first line is processed as TODO.
2. With Gluing Enabled all three lines are processed as one TODO comment.

## Rules

### 1. Single-Line Comments Only

- Only consecutive single-line comments are glued.
- Multi-line comments (`/* */`, `/** */`) are not affected. And they are breaks consequence when mixed with single-line comments.

### 2. Empty Line Breaks Sequence

Single line break between comments is allowed:

```php
// TODO: first line
//       second line
```
✅ Glued together

Multiple line breaks (empty line) break the sequence:

```php
// TODO: first line

//       second line
```
❌ Not glued (empty line between)

### 3. Indentation Ignored

Different indentation doesn't prevent gluing:

```php
// Comment 1
    // Comment 2 (different indentation)
        // Comment 3
```
✅ All glued together

### Multiple TODOs

```php
// TODO: Task 1
// TODO: Task 2 description
//       with more details
```

With gluing enabled:
- Creates ONE composite comment from all three lines
- Extractor finds TWO separate TODOs within that comment
- Both TODOs get registered and keys injected correctly

## Important Notes

- Original line break style is preserved (`\n`, `\r\n`, or mixed)
- Trailing whitespace in comments is preserved
- Works with all supported issue trackers
