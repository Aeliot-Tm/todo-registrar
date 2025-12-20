# Architecture Overview

## Purpose

TODO Registrar is a CLI application that scans PHP source code for TODO/FIXME comments and automatically registers them as issues in external issue trackers (JIRA, GitHub, GitLab).

## High-Level Flow

1. **Entry Point** → `bin/todo-registrar` initializes the Symfony Console application
2. **Configuration Loading** → Reads `.todo-registrar.yaml` or `.todo-registrar.php` config file
3. **File Discovery** → Uses Symfony Finder to locate PHP files
4. **Tokenization** → PHP Tokenizer extracts comment tokens from source files
5. **Comment Extraction** → Parses comments to find TODO/FIXME tags
6. **Issue Registration** → Creates issues in configured issue tracker via API
7. **Key Injection** → Injects created issue key back into the source comment

## Core Components

### Application Bootstrap

```
bin/todo-registrar
    └── Console/Application.php (extends Symfony Console)
        └── Console/ContainerBuilder.php (builds Symfony DI Container from config/services.yaml)
```

### Command Layer

- `Console/Command/RegisterCommand.php` — Main command that orchestrates the registration process

### Service Layer

#### Configuration Services (`Service/Config/`)
- `ConfigProvider` — Provides configuration from file path
- `ConfigFactory` — Creates Config object from YAML/PHP files
- `ArrayConfigFactory` — Converts array config to Config object
- `ConfigFileGuesser` — Auto-detects config file location

#### File Processing Services (`Service/File/`)
- `Finder` — Implements `FinderInterface`, wraps Symfony Finder for file discovery
- `Tokenizer` — Tokenizes PHP files into PhpToken arrays
- `Saver` — Saves modified files back to disk

#### Comment Processing Services (`Service/Comment/`)
- `Detector` — Filters PhpTokens to find T_COMMENT and T_DOC_COMMENT tokens
- `Extractor` — Extracts TODO/FIXME parts from comment text

#### Tag Detection (`Service/Tag/`)
- `Detector` — Parses tag metadata (tag name, assignee, existing ticket key)

#### Core Processing
- `HeapRunner` — Main processing loop: iterates files → comments → TODOs → register → inject key
- `HeapRunnerFactory` — Factory for HeapRunner with all dependencies
- `TodoBuilder` / `TodoBuilderFactory` — Creates Todo DTO from CommentPart

#### Registrar Services (`Service/Registrar/`)
- `RegistrarProvider` — Resolves registrar from config
- `RegistrarFactoryRegistry` — Service locator for registrar factories

### Registrar Implementations

Each issue tracker has its own implementation in subdirectories:

#### JIRA (`Service/Registrar/JIRA/`)
- `JiraRegistrar` — Implements `RegistrarInterface`
- `JiraRegistrarFactory` — Implements `RegistrarFactoryInterface`
- `IssueFieldFactory` — Creates JIRA issue fields from Todo
- `ServiceFactory` — Creates JIRA API clients
- `IssueLinkRegistrar` — Registers issue links

#### GitHub (`Service/Registrar/GitHub/`)
- `GitHubRegistrar` — Implements `RegistrarInterface`
- `GitHubRegistrarFactory` — Implements `RegistrarFactoryInterface`
- `IssueFactory` — Creates GitHub issue from Todo
- `ApiClientFactory` — Creates GitHub API clients

#### GitLab (`Service/Registrar/GitLab/`)
- `GitlabRegistrar` — Implements `RegistrarInterface`
- `GitlabRegistrarFactory` — Implements `RegistrarFactoryInterface`
- `IssueFactory` — Creates GitLab issue from Todo
- `ApiClientProvider` — Provides GitLab API clients

### Contracts (Interfaces)

Located in `Contracts/`:

| Interface | Purpose |
|-----------|---------|
| `RegistrarInterface` | Contract for issue registration (`register(TodoInterface): string`) |
| `RegistrarFactoryInterface` | Contract for creating registrars from config |
| `TodoInterface` | Contract for TODO data transfer object |
| `FinderInterface` | Contract for file finder (iterable over SplFileInfo) |
| `GeneralConfigInterface` | Contract for application configuration |
| `InlineConfigInterface` | Contract for inline config in comments |
| `InlineConfigFactoryInterface` | Contract for creating inline config |
| `InlineConfigReaderInterface` | Contract for reading inline config from comment |

### DTOs

Located in `Dto/`:

- `Registrar/Todo` — Main data object for TODO comment
- `Comment/CommentPart` — Represents a part of a comment (single TODO)
- `Comment/CommentParts` — Collection of CommentPart objects
- `Tag/TagMetadata` — Parsed tag metadata (tag name, assignee, ticket key)
- `FileHeap` — File processing context with tokens and update callback
- `ProcessStatistic` — Statistics of processing (files updated, TODOs registered)
- `InlineConfig/*` — Inline configuration structures

### Enums

- `Enum/RegistrarType` — Supported issue tracker types: GitHub, GitLab, JIRA, etc.

## Design Patterns

| Pattern | Usage |
|---------|-------|
| **Factory** | `HeapRunnerFactory`, `TodoBuilderFactory`, `*RegistrarFactory` |
| **Strategy** | `RegistrarInterface` implementations for different issue trackers |
| **Service Locator** | `RegistrarFactoryRegistry` with Symfony's `#[AutowireLocator]` |
| **Dependency Injection** | Symfony DI Container with autowiring |
| **Iterator** | `FinderInterface` extends `IteratorAggregate` for file traversal |

## Configuration

### Config File Formats
- YAML: `.todo-registrar.yaml` (preferred)
- PHP: `.todo-registrar.php` (for programmatic config)

### Key Configuration Options
- `finder` — Symfony Finder configuration (paths, patterns)
- `registrar` — Issue tracker type and credentials
- `tags` — TODO tags to detect (default: `['todo', 'fixme']`)
- `inline_config` — Inline configuration options

## Directory Structure

```
src/
├── Config.php                      # Main configuration class
├── Console/                        # CLI layer
│   ├── Application.php
│   ├── Command/RegisterCommand.php
│   ├── ContainerBuilder.php
│   └── OutputAdapter.php
├── Contracts/                      # Interfaces
├── Dto/                            # Data Transfer Objects
│   ├── Comment/
│   ├── InlineConfig/
│   ├── Registrar/
│   └── Tag/
├── Enum/                           # Enumerations
├── Exception/                      # Custom exceptions
└── Service/                        # Business logic
    ├── Comment/                    # Comment parsing
    ├── Config/                     # Configuration loading
    ├── File/                       # File operations
    ├── InlineConfig/               # Inline config parsing
    ├── Registrar/                  # Issue tracker integrations
    │   ├── GitHub/
    │   ├── GitLab/
    │   └── JIRA/
    └── Tag/                        # Tag detection
```

## Extending

### Adding New Issue Tracker

1. Create new directory in `Service/Registrar/{TrackerName}/`
2. Implement `RegistrarInterface` for issue creation
3. Implement `RegistrarFactoryInterface` with `#[AutoconfigureTag('aeliot.todo_registrar.registrar_factory')]`
4. Add case to `Enum/RegistrarType`

### Custom Inline Config

Implement `InlineConfigFactoryInterface` and `InlineConfigReaderInterface` to customize how inline config is parsed from comments.
