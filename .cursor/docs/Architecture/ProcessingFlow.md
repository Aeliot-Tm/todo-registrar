# Processing Flow

Main algorithm from file discovery to issue registration and source update. Implemented in `HeapRunner`.

## Flow Diagram

```
HeapRunner.run()
    │
    ├─► Finder (SplFileInfo)
    │
    ├─► FileParserRegistry → ParsedFile (tokens + context map)
    │
    ├─► FileHeap.buildCommentNodes()  [optional sequential gluing]
    │
    ├─► Comment/Extractor → CommentPart[]
    │       └─ skip if tag line has ticketKey
    │
    ├─► TodoBuilder → Todo
    │
    ├─► Registrar.register()  [optional same-ticket gluing by hash]
    │
    ├─► Todo.injectKey() → CommentPart updates token text
    │
    └─► Saver.save() via fileUpdateCallback
```

Processing uses generators: one file in memory at a time; file saved after each registration.

## Step 1: File Discovery

**Class:** `Service/File/Finder`

Iterates configured paths via Symfony Finder.

YAML config (`paths`):

- `in`, `append`, `exclude`, `extensions`, `name`
- Default extensions: `php`, `yaml`, `yml`

PHP config: configure Finder directly.

## Step 2: File Parsing

**Classes:** `FileParserRegistry`, `PhpFileParser`, `YamlFileParser`

Extension resolution:

1. Lowercase file extension
2. Map via `process.extensionAliases` if set
3. Select parser from registry; skip file if none

Output: `ParsedFile` with:

- `getAllTokens()` — `TokenInterface[]` (mutable via `setText()`)
- Lazy context map for AST/YAML structure

See [Source File Parsing](../Feature/SourceFileParsing.md).

## Step 3: Comment Node Building

**Class:** `Dto/FileHeap`

Single pass over all tokens:

| Token | Action (gluing enabled) |
|---|---|
| Single-line comment | Add to `CommentTokensGroup` |
| Whitespace only | Buffer if group active; multiple line breaks flush group |
| Non-empty non-comment | Flush group |
| Multi-line comment | Flush group; create node for block comment alone |

Gluing disabled: each comment token becomes its own `CommentNode` immediately.

`CommentNode` wraps one or more tokens + `MappedContext` at first token line.

## Step 4: TODO Extraction

**Classes:** `Comment/Extractor`, `Tag/Detector`, `CommentCleanerRegistry`

For each `CommentNode`:

1. Split comment tokens into lines (PHP or YAML cleaner)
2. Detect configured tags (`tags` config, default `todo`, `fixme`) on each line
3. Group continuation lines (whitespace-only prefix before content)
4. Yield `CommentPart` per TODO

Tag pattern (simplified): optional comment prefix, `@?tag`, optional `@assignee`, optional separator, optional existing key.

Separators from `issueKeyInjection.summarySeparators` (default `:`, `-`, `>`).

## Step 5: Skip Registered

```php
if ($commentPart->getTagMetadata()?->getTicketKey()) {
    // skip — already has key
}
```

## Step 6: Build Todo

**Class:** `TodoBuilder`

Creates `ContextAwareTodo` (implements `Todo`):

- tag, summary, description, assignee
- context from `CommentNode`
- hash for same-ticket gluing
- inline config from `{EXTRAS: ...}`
- issue key injection settings

## Step 7: Register Issue

**Class:** `HeapRunner::register()`

If `process.glueSameTickets` and hash seen → reuse key, `tickGluedTodo()`.
Else → `registrar->register($todo)`, store hash → key mapping.

Errors wrapped in `CommentRegistrationException` with comment line and content.

| Registrar | Returned key |
|---|---|
| JIRA | `PROJ-123` |
| GitHub / GitLab / Redmine | `#123` |
| Yandex Tracker | `QUEUE-123` |

## Step 8: Inject Key

**Class:** `CommentPart::injectKey()`

Modifies first line at configured `IssueKeyPosition`; updates underlying tokens via `TokenLinesStack::flush()`.

See [Issue Key Injection](../Feature/IssueKeyInjection.md).

## Step 9: Save File

**Class:** `Service/File/Saver`

```php
implode('', array_map(fn ($t) => $t->getText(), $tokens))
```

Called from `FileHeap` closure after each successful registration for that file.

## Statistics

**Class:** `ProcessStatistic`

Tracks per run: analyzed/updated files, comment tokens, ignored/glued/registered TODOs, per-file registration counts.

Optional export via [Report](../Feature/Report.md).

## Generator Chain

```
run()
  └── getTodos()                 → [Todo, fileUpdateCallback]
        └── getCommentParts()    → [CommentPart, fileUpdateCallback]
              └── getFileHeaps() → FileHeap
                    └── finder   → SplFileInfo
```

## Related Features

- [Sequential Comments Gluing](../Feature/SequentialCommentsGluing.md)
- [Same-Ticket Gluing](../Feature/SameTicketGluing.md)
- [Issue Key Injection](../Feature/IssueKeyInjection.md)
