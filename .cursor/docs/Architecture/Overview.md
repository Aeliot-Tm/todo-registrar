# Architecture Overview

## Purpose

TODO Registrar is a CLI application that scans PHP and YAML source files for TODO/FIXME comments and registers them as issues in external issue trackers.

Implemented registrars: GitHub, GitLab, JIRA, Redmine, Yandex Tracker.

## High-Level Flow

1. **Entry** — `bin/todo-registrar` → Symfony Console `Application`
2. **Configuration** — `.todo-registrar.yaml` / `.yml`, `.todo-registrar.php`, or YAML via `--config=STDIN`
3. **File Discovery** — Symfony Finder (`paths` config or PHP Finder)
4. **Parsing** — `FileParserRegistry` → `PhpFileParser` or `YamlFileParser` → `ParsedFile`
5. **Comment grouping** — `FileHeap` (optional sequential comment gluing)
6. **TODO extraction** — `Comment/Extractor` + `Tag/Detector`
7. **Registration** — `RegistrarInterface` (optional same-ticket gluing)
8. **Key injection** — `CommentPart::injectKey()` → `Saver` writes file

## Core Components

### Application Bootstrap

```
bin/todo-registrar
    └── Console/Application.php
        └── Console/ContainerBuilder.php  (DI from config/services.yaml)
```

### Command Layer

- `Console/Command/RegisterCommand.php` — orchestrates run and optional report export

### Configuration Services (`Service/Config/`)

| Class | Role |
|---|---|
| `ConfigProvider` | Loads config from path, auto-detect, or STDIN |
| `ConfigFactory` | Parses `.yaml`/`.yml` via `YamlParser` + `ArrayConfigFactory` |
| `ArrayConfigFactory` | Validates array → `Config` object |
| `StdinConfigFactory` | YAML from stdin |
| `ConfigFileGuesser` / `ConfigFileDetector` | Auto-detect config file |

PHP config: `require` must return `GeneralConfigInterface`.

### File Processing (`Service/File/`)

| Class | Role |
|---|---|
| `Finder` | Symfony Finder wrapper (`FinderInterface`) |
| `FileParserRegistry` | Selects parser by extension |
| `PhpFileParser` | PHP tokens + AST context |
| `YamlFileParser` | YAML tokens + context (`aeliot/yaml-token`) |
| `Saver` | Rebuilds file from `TokenInterface[]` |

### Comment Processing

| Class | Role |
|---|---|
| `FileHeap` | Builds `CommentNode[]`, glues sequential comments via glue gates |
| `SequentialCommentGlueGateRegistry` | PHP/YAML rules for sequential comment gluing |
| `Comment/Extractor` | Splits comments into `CommentPart[]` |
| `Tag/Detector` | Parses tag, assignee, existing key |
| `CommentCleanerRegistry` | PHP and YAML comment line cleaners |

### Core Processing

| Class | Role |
|---|---|
| `HeapRunner` | Main loop: files → comments → todos → register → save |
| `HeapRunnerFactory` | Wires dependencies from config |
| `TodoBuilder` | Builds `Todo` DTO with hash and inline config |

### Registrar Services (`Service/Registrar/`)

| Class | Role |
|---|---|
| `RegistrarProvider` | Resolves active registrar |
| `RegistrarFactoryRegistry` | Locator for `*RegistrarFactory` |
| `IssueSupporter` | Shared summary, description, labels, assignees |

Subdirectories: `GitHub/`, `GitLab/`, `JIRA/`, `Redmine/`, `YandexTracker/`.

### Contracts Package

`aeliot/todo-registrar-contracts` — `RegistrarInterface`, `TodoInterface`, `ContextAwareInterface`, `GeneralConfigInterface`, `TokenInterface` (via adapters), etc.

### Key DTOs (`Dto/`)

- `Registrar/Todo`, `Registrar/ContextAwareTodo` (deprecated alias)
- `Comment/CommentPart`
- `Parsing/ParsedFile`, `Parsing/CommentNode`, `Parsing/MappedContext`
- `FileHeap`, `ProcessStatistic`
- `Token/PhpTokenAdapter`, `Token/YamlTokenAdapter`
- `GeneralConfig/*` — validated config sections

## Configuration

### Config File Formats
- YAML: `.todo-registrar.yaml` (preferred)
- PHP: `.todo-registrar.php` (for programmatic config)
- STDIN: Pass YAML via `--config=STDIN` option

## Configuration Shape (YAML)

Top-level keys: `paths`, `registrar`, `tags`, `issueKeyInjection`, `process`.

```yaml
paths:
  in: src
  extensions: [php, yaml, yml]

registrar:
  type: GitHub
  options:
    service: { ... }
    issue: { ... }

tags: [todo, fixme]

issueKeyInjection:
  position: after_separator

process:
  glueSameTickets: false
  glueSequentialComments: false
  extensionAliases: {}
```

`Enum/RegistrarType` also lists `AzureBoards` and `YouTrack` — no factory implementations exist yet.

### STDIN Configuration

When `--config=STDIN` is passed, the application reads YAML configuration from STDIN.
This is useful for Docker environments or CI/CD pipelines where configuration can be piped:

```bash
cat config.yaml | docker compose exec -T php-cli ./bin/todo-registrar register --config=STDIN
```

The `-T` flag is required with `docker compose exec` to disable TTY allocation for STDIN to work.

## Directory Structure

```
src/
├── AST/PHP/                        # PHP context map
├── AST/YAML/                       # YAML context map
├── Config.php                      # Main configuration class
├── Console/                        # CLI layer
├── Dto/                            # Data Transfer Objects
├── Enum/                           # Enumerations
├── Exception/                      # Custom exceptions
└── Service/                        # Business logic
    ├── Comment/                    # Comment parsing
    ├── Config/                     # Configuration loading
    ├── ContextPath/
    ├── File/                       # File operations
    ├── InlineConfig/               # Inline config parsing
    ├── Registrar/                  # Issue tracker integrations
    ├── Report/
    └── Tag/                        # Tag detection
```

## Extending

### Adding New Issue Tracker

1. Add `Service/Registrar/{Name}/` with `*Registrar` and `*RegistrarFactory`
2. Tag factory: `#[AsTaggedItem(index: RegistrarType::{Name}->value)]`
3. Add enum case to `RegistrarType`

### Custom Inline Config

Implement `InlineConfigFactoryInterface` and `InlineConfigReaderInterface` (package root namespace) to customize how inline config is parsed from comments.

### New Source File Type

1. Implement `FileParserInterface`
2. Tag with `aeliot.todo_registrar.file_parser` and extension index
3. Provide `TokenInterface` adapter and optional context map builder

See [Processing Flow](ProcessingFlow.md), [Source File Parsing](../Feature/SourceFileParsing.md).
