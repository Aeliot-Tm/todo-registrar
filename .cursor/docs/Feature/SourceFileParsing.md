# Source File Parsing

Scans PHP and YAML source files for TODO/FIXME comments, tokenizes them, and builds a context map for each file.

## What It Does

1. Discovers files via Symfony Finder (`paths` config or PHP Finder setup)
2. Selects a parser by file extension (`FileParserRegistry`)
3. Produces `ParsedFile`: all tokens (`TokenInterface[]`) and a lazy context map
4. Passes the result to `FileHeap` for comment grouping and further processing

Supported extensions by default: `php`, `yaml`, `yml`.

## Parser Selection

`HeapRunner` resolves the parser key as follows:

1. Take the file extension (lowercase)
2. Apply `process.extensionAliases` if configured (e.g. `module: php`)
3. Look up the parser in `FileParserRegistry`

If no parser is registered for the extension, the file is skipped with an error message.

## PHP Files

Parser: `PhpFileParser`

- Tokenizes with PHP's built-in `PhpToken::tokenize()`
- Wraps tokens in `PhpTokenAdapter` (`TokenInterface`)
- Builds AST context map via `nikic/php-parser` (`AST/PHP/ContextMapBuilder`)
- Supports single-line (`//`, `#`) and multi-line (`/* */`, `/** */`) comments

## YAML Files

Parser: `YamlFileParser`

- Parses with `aeliot/yaml-token` (`ParserBuilder`)
- Wraps comment nodes in `YamlTokenAdapter` (`TokenInterface`)
- Builds context map via `AST/YAML/ContextMapBuilder` (document, mapping keys, sequence items)
- Supports `#` comments only; all YAML comments are treated as single-line

## Configuration

```yaml
paths:
  in: src
  extensions: [php, yaml, yml]   # default when omitted
  exclude: vendor
  name: '/\.(?:php|ya?ml)$/'

process:
  extensionAliases:
    module: php                    # treat .module files as PHP
```

In PHP config, set Finder masks directly (for example `->name('/\.(?:php|yaml|yml)$/')`).

## Technical Details

| Class | Path | Role |
|---|---|---|
| `FileParserRegistry` | `src/Service/File/FileParserRegistry.php` | Service locator for parsers |
| `PhpFileParser` | `src/Service/File/Parser/PhpFileParser.php` | PHP tokenization + AST |
| `YamlFileParser` | `src/Service/File/Parser/YamlFileParser.php` | YAML tokenization + context |
| `PhpTokenAdapter` | `src/Dto/Token/PhpTokenAdapter.php` | Mutable PHP token wrapper |
| `YamlTokenAdapter` | `src/Dto/Token/YamlTokenAdapter.php` | Mutable YAML comment wrapper |
| `ParsedFile` | `src/Dto/Parsing/ParsedFile.php` | Parser output DTO |
| `Saver` | `src/Service/File/Saver.php` | Rebuilds file from tokens after key injection |

Parsers are registered in DI with tag `aeliot.todo_registrar.file_parser` and indexed by extension key (`php`, `yaml`).

Related features: [Sequential Comments Gluing](SequentialCommentsGluing.md), [Context Display](ContextDisplay.md).
