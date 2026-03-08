# Context Display

Adds code context information (file, namespace, class, method, etc.) to issue descriptions, helping understand where a TODO comment is located without opening the file.

## What It Does

1. When creating an issue, builds a context path from the outermost level (file) to the innermost level (where the TODO is located)
2. Formats the context path according to the selected format
3. Prepends the formatted context to the issue description

## Supported Formats

| Value | Description |
|---|---|
| `null` (default) | Context is not displayed |
| `arrow_chained` | Single line with `->` separators |
| `asterisk` | Multi-line list with `*` prefix |
| `code_block` | Multi-line code block |
| `number_sign` | Multi-line list with `#` prefix |
| `numbered` | Numbered multi-line list |

## Configuration

Configured via `showContext` option in the registrar's `issue` section.
Can be overridden per-TODO via inline config.
Optional `contextTitle` adds a title above the context block.

See [user documentation](../../../docs/context_display.md) for format examples, context types, and configuration details.

## Key Source Paths

- Context path builders: `src/Service/ContextPath/` (one builder per format)
- Format enum: `src/Enum/ContextPathBuilderFormat.php`
- Context map: `src/Dto/Parsing/LazyContextMap.php`
- Context map visitor: `src/Dto/Parsing/ContextMapVisitor.php`
- Issue supporter (integration): `src/Service/Registrar/IssueSupporter.php`
