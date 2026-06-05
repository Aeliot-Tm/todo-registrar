# Processing Flow

Main algorithm from file discovery to issue registration and source update. Implemented in `HeapRunner`.

## Flow Diagram

```
HeapRunner.run()
    │
    ├─► HeapContext (ProcessStatistic, hashToKey, glueSameTickets)
    │
    └─► foreach Finder (SplFileInfo)
            │
            ├─► FileHeapFactory.create()
            │       ├─► FileParserRegistry → ParsedFile (tokens + context map)
            │       └─► CommentNodesBuilder.build()  [optional sequential gluing]
            │
            ├─► processFile()
            │       ├─► Comment/Extractor → CommentPart[]
            │       │       └─ skip if tag line has ticketKey
            │       ├─► TodoBuilder → Todo
            │       ├─► Registrar.register()  [optional same-ticket gluing by hash]
            │       ├─► Todo.injectKey() → CommentPart updates token text
            │       └─► FileHeap.saveAfterRegistration() → Saver.save()
            │
            ├─► logFileCompletion()
            │
            └─► on Exception: writeError() + rethrow (fail-fast)
```

One file in memory at a time; file saved after each registration. The main loop is imperative;
generators are used only in local helpers (e.g. `Comment/Extractor::getLines()`).

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

- `getTokenStream()` — cursor over the same token list (`current`, `advance`, `peek`)
  which allows iterate `TokenInterface` (mutable via `setText()`)
- Lazy context map for AST/YAML structure

See [Source File Parsing](../Feature/SourceFileParsing.md).

## Step 3: Comment Node Building

**Classes:** `Dto/FileHeap`, `Service/Comment/CommentNodesBuilder`, `Service/Comment/SequentialCommentGlueGateRegistry`

Single pass via `ParsedFile::getTokenStream()`:

| Token | Action (gluing enabled) |
|---|---|
| Token accepted by glue gate | Add to `CommentTokensGroup` (comment or whitespace between glued lines) |
| Non-glueable whitespace with active group | Flush group |
| Non-empty non-comment | Flush group |
| Non-glueable comment (e.g. PHP block) | Flush group; create node for that comment alone |

Glue gate is selected by file extension alias (`php`, `yaml`, `yml`). Lookahead on the stream decides whether whitespace bridges to the next glueable comment.

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

Run-scoped state lives in `HeapContext` (`statistic`, `hashToKey`, `glueSameTickets`).

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

**Classes:** `Dto/FileHeap`, `Service/File/Saver`

```php
implode('', array_map(fn ($t) => $t->getText(), $tokens))
```

`FileHeap::saveAfterRegistration()` updates per-file statistics and writes the file after each successful registration.

## Statistics

**Class:** `ProcessStatistic`

Tracks per run: analyzed/updated files, comment tokens, ignored/glued/registered TODOs, per-file registration counts.

Optional export via [Report](../Feature/Report.md).

## Processing Loop

```
run()
  ├── new HeapContext
  └── foreach finder → SplFileInfo
        try
          ├── FileHeapFactory.create() → FileHeap (or skip if no parser)
          ├── processFile()
          │     └── foreach commentNode
          │           └── foreach CommentPart
          │                 ├── TodoBuilder → Todo
          │                 ├── register() + saveAfterRegistration()
          │                 └── CommentRegistrationException propagates up
          └── logFileCompletion()
        catch Exception
          ├── writeError($exception, $file)
          └── rethrow
```

Per-file processing is wrapped in a single `try/catch` in `run()`. Any `\Exception`
(parse, glue gate, registration, todo building) triggers `writeError()` with the current
file path and stops the run (fail-fast). `register()` wraps registrar failures in
`CommentRegistrationException` before they reach the outer catch.

## HeapContext

**Class:** `Dto/HeapContext`

Mutable run-scoped bag passed through `FileHeapFactory.create()`, `processFile()`, and `register()`:

| Property | Purpose |
|---|---|
| `statistic` | `ProcessStatistic` for the whole run |
| `hashToKey` | Hash → issue key map for same-ticket gluing |
| `glueSameTickets` | From `process.glueSameTickets` config |
| `output` | Console output adapter for the run |

Created once in `run()`; shared across all files in the run.

## Error Handling

| Situation | Behavior |
|---|---|
| No parser for file extension | `writeErr`, skip file (`continue`) |
| Parse / glue gate / registration / build error | `writeError($exception, $file)` in `run()`, rethrow |
| Registrar failure | Wrapped in `CommentRegistrationException` in `register()` |

## Related Features

- [Sequential Comments Gluing](../Feature/SequentialCommentsGluing.md)
- [Same-Ticket Gluing](../Feature/SameTicketGluing.md)
- [Issue Key Injection](../Feature/IssueKeyInjection.md)
