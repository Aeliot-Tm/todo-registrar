# Context Display

Appends code location context to the issue description so the reader can see where the TODO comment lives without opening the file.

## What It Does

1. Resolves `showContext` from inline config or `issue.showContext` in registrar config
2. When set, appends `\n\n`, optional `contextTitle`, and a formatted context path to the issue body
3. Context path is built from the outermost scope (file/document) down to the innermost node at the comment line

Works for TODO comments in both PHP and YAML source files.

## Supported Formats

| Value | Output style |
|---|---|
| `null` (default) | Context not appended |
| `arrow_chained` | Single line joined with ` -> ` |
| `asterisk` | Multi-line list with `*` prefix |
| `code_block` | Multi-line fenced-style block |
| `number_sign` | Multi-line list with `#` prefix |
| `numbered` | Numbered multi-line list |

## Context Node Labels

**PHP** (via `PhpContextNodeInterface`): File, Namespace, Class, Interface, Trait, Enum, Method,
Function, Closure, Arrow function, Property, Constant, Parameter, Match expression, Enum case, and others.

**YAML** (via `YamlContextNodeInterface`): Document, Key, Sequence item.

## Configuration

```yaml
registrar:
  options:
    issue:
      showContext: numbered
      contextTitle: 'Location'
```

Per-TODO override in inline config: `showContext`, `contextTitle`.

See [user documentation](../../../docs/context_display.md) for format examples, context types, and configuration details.

## Technical Details

| Component | Path |
|---|---|
| Integration | `src/Service/Registrar/IssueSupporter.php` (`getDescription()`) |
| Format enum | `src/Enum/ContextPathBuilderFormat.php` |
| Builders | `src/Service/ContextPath/Builder/` |
| Registry | `src/Service/ContextPath/ContextPathBuilderRegistry.php` |
| PHP context map | `src/AST/PHP/ContextMapBuilder.php`, `ContextMapVisitor.php` |
| YAML context map | `src/AST/YAML/ContextMapBuilder.php`, `ContextMapVisitor.php` |
| Context at comment line | `src/Dto/Parsing/MappedContext.php` |
