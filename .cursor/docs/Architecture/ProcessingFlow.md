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
            ├─► FileProcessor.process()
            │       ├─► Comment/Extractor → CommentPart[]
            │       │       └─ skip if tag line has ticketKey
            │       ├─► TodoBuilder → Todo
            │       ├─► Registrar.register()  [optional same-ticket gluing by hash]
            │       ├─► Todo.injectKey() → CommentPart updates token text
            │       └─► FileHeap.saveAfterRegistration() or recordRegistration() in --dry-run
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

- `in`, `append`, `exclude`, `extensions`, `name`, `sortByName`
- Default extensions: `php`, `yaml`, `yml`
- Default `sortByName`: `true` (files sorted by path name)

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

**Class:** `FileProcessor`

Run-scoped state lives in `HeapContext` (`statistic`, `hashToKey`, `glueSameTickets`).

If `process.glueSameTickets` and hash seen → reuse key, `tickGluedTodo()`.
Else → `registrar->register($todo)`, store hash → key mapping.

With `--dry-run`, `HeapRunnerFactory` selects `DryRunRegistrar` (via `RegistrarType::DryRun`) and clears registrar
config; fake keys `#dry-run-N` are returned and the configured tracker is not used. Config may also set
`registrar.type: DryRun` for a minimal setup without tracker credentials — see [DryRun Registrar](../Feature/DryRunRegistrar.md).

Errors wrapped in `CommentRegistrationException` with comment line and content.

| Registrar | Returned key |
|---|---|
| DryRun | `#dry-run-N` |
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

With `--dry-run`, `FileHeap::recordRegistration()` updates statistics only; `Saver` is not called.

## Statistics

**Classes:** `ProcessStatistic`, `ProcessMeta`

Tracks per run:

- analyzed/updated files, comment tokens, ignored/glued/registered TODOs
- `newIssues` (registered − glued)
- per-file registration counts
- issue key usage (`key` → `usageCounter`) for each inserted key
- run metadata (`dryRun` flag) via `ProcessMeta`

`FileProcessor::register()` calls `tickIssueKeyUsage()` on every key injection (new registration or same-ticket glue
reuse). Ignored TODOs with a pre-existing key in the comment are counted in `ignored` but not added to the issue key
list.

Optional export via [Report](../Feature/Report.md) (`meta`, `summary`, `issues`, `files`).

## Processing Loop

```
run()
  ├── HeapContextFactory.create(config, output)
  └── foreach finder → SplFileInfo
        try
          ├── FileHeapFactory.create() → FileHeap (or skip if no parser)
          ├── FileProcessor.process()
          │     └── foreach commentNode
          │           └── foreach CommentPart
          │                 ├── TodoBuilder → Todo
          │                 ├── FileProcessor.register() + saveAfterRegistration()
          │                 └── CommentRegistrationException propagates up
          └── logFileCompletion()
        catch Exception
          ├── writeError($exception, $file)
          └── rethrow
```

Per-file processing is wrapped in a single `try/catch` in `run()`. Any `\Exception`
(parse, glue gate, registration, todo building) triggers `writeError()` with the current
file path and stops the run (fail-fast). `FileProcessor` wraps registrar failures in
`CommentRegistrationException` before they reach the outer catch.

## HeapContext

**Class:** `Dto/HeapContext`

Mutable run-scoped bag passed through `FileHeapFactory.create()`, `FileProcessor.process()`, and registration:

| Property | Purpose |
|---|---|
| `statistic` | `ProcessStatistic` for the whole run |
| `hashToKey` | Hash → issue key map for same-ticket gluing |
| `glueSameTickets` | From `process.glueSameTickets` config |
| `isDryRun` | From `--dry-run` CLI flag |
| `output` | Console output adapter for the run |

Built once in `run()` via `HeapContextFactory`; shared across all files in the run.

## Error Handling

| Situation | Behavior |
|---|---|
| No parser for file extension | `writeErr`, skip file (`continue`) |
| Parse / glue gate / registration / build error | `writeError($exception, $file)` in `run()`, rethrow |
| Registrar failure | Wrapped in `CommentRegistrationException` in `FileProcessor` |

## Related Features

- [Sequential Comments Gluing](../Feature/SequentialCommentsGluing.md)
- [Same-Ticket Gluing](../Feature/SameTicketGluing.md)
- [Issue Key Injection](../Feature/IssueKeyInjection.md)
