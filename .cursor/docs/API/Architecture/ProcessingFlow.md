# Processing Flow

## Overview

This document describes the main algorithm of TODO comment processing — from file discovery to issue registration and source code update.

## High-Level Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HeapRunner.run()                               │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  1. ITERATE FILES (Finder)                                                  │
│     foreach ($finder as $file)                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  2. TOKENIZE FILE (Tokenizer)                                               │
│     PhpToken::tokenize($fileContents) → PhpToken[]                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  3. FILTER COMMENT TOKENS (CommentDetector)                                 │
│     Keep only T_COMMENT and T_DOC_COMMENT tokens                            │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  4. EXTRACT TODO PARTS (CommentExtractor)                                   │
│     foreach ($commentTokens as $token)                                      │
│         Split comment into lines                                            │
│         Detect TODO/FIXME tags (TagDetector)                                │
│         Build CommentParts with CommentPart objects                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  5. SKIP ALREADY REGISTERED (check ticketKey)                               │
│     if (commentPart.getTagMetadata().getTicketKey()) → skip                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  6. BUILD TODO DTO (TodoBuilder)                                            │
│     Create Todo from CommentPart with:                                      │
│     - tag, summary, description, assignee                                   │
│     - inlineConfig (parsed from description)                                │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  7. REGISTER ISSUE (Registrar)                                              │
│     Call external API (JIRA/GitHub/GitLab)                                  │
│     Returns issue key (e.g., "PROJ-123", "#42")                             │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  8. INJECT KEY INTO COMMENT (CommentPart.injectKey)                         │
│     Modify comment text: "TODO:" → "TODO: PROJ-123"                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  9. UPDATE TOKEN TEXT                                                       │
│     token.text = commentParts.getContent()                                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  10. SAVE FILE (Saver)                                                      │
│      Rebuild file content from all tokens                                   │
│      Write to disk                                                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Detailed Step-by-Step Description

### Step 1: File Discovery

**Class:** `Service\File\Finder` (implements `FinderInterface`)

The Finder iterates over PHP files in configured directories using Symfony Finder.

```php
foreach ($this->finder as $file) {
    // $file is SplFileInfo
}
```

Configuration determines:
- Which directories to scan
- File patterns to include/exclude
- Recursion depth

### Step 2: File Tokenization

**Class:** `Service\File\Tokenizer`

Each file is tokenized using PHP's built-in tokenizer:

```php
$tokens = PhpToken::tokenize(file_get_contents($file->getPathname()));
```

Result: array of `PhpToken` objects representing all PHP tokens in the file.

**Important:** Tokens are mutable objects. Modifying `$token->text` directly affects the token that will be used when saving the file.

### Step 3: Filter Comment Tokens

**Class:** `Service\Comment\Detector`

Filters tokens to keep only comments:

```php
$commentTokens = array_filter($tokens, fn($token) =>
    in_array($token->id, [T_COMMENT, T_DOC_COMMENT])
);
```

| Token Type | Description | Example |
|---|---|---|
| `T_COMMENT` | Single-line or multi-line comment | `// comment`, `/* comment */` |
| `T_DOC_COMMENT` | PHPDoc comment | `/** @param ... */` |

### Step 4: Extract TODO Parts

**Classes:** `Service\Comment\Extractor`, `Service\Tag\Detector`

For each comment token:

1. **Split into lines** — preserving line endings (CR, LF, CRLF)
2. **Detect tag on each line** — using regex pattern for TODO/FIXME
3. **Group lines into CommentPart** — consecutive lines belong to same TODO if they have proper prefix

**Tag Detection Pattern:**
```regex
~^([\s\#*/]*@?(?P<tag>todo|fixme)(?:@(?P<assignee>[a-z0-9._-]+))?...)~ix
```

Detects:
- Tag name (TODO, FIXME)
- Optional assignee (`@username`)
- Optional existing ticket key (to skip already registered)

**Result:** `CommentParts` object containing:
- `parts[]` — all comment parts (with and without tags)
- `todos[]` — only parts with TODO/FIXME tags

### Step 5: Skip Already Registered

Before processing, check if TODO already has a ticket key:

```php
$ticketKey = $commentPart->getTagMetadata()?->getTicketKey();
if ($ticketKey) {
    // Skip — already registered
    continue;
}
```

Recognized ticket key formats:
- JIRA: `PROJ-123`
- GitHub/GitLab: `#123`
- Date: `2024-12-31`
- Version: `v1.2.3`
- Package version: `symfony/console:^7.0`

### Step 6: Build Todo DTO

**Class:** `Service\TodoBuilder`

Creates `Todo` DTO from `CommentPart`:

```php
$todo = new Todo(
    tag: $commentPart->getTag(),           // "TODO" or "FIXME"
    summary: $commentPart->getSummary(),    // First line after tag
    description: $commentPart->getDescription(), // Rest of lines
    assignee: $tagMetadata->getAssignee(), // From @username
    commentPart: $commentPart,             // Reference for key injection
    inlineConfig: $inlineConfig,           // Parsed {EXTRAS: {...}}
);
```

**Inline Config Parsing:**
- Reads `{EXTRAS: {...}}` from description
- Parses JSON-like syntax
- Returns `InlineConfigInterface` with parsed values

### Step 7: Register Issue

**Interface:** `RegistrarInterface`

```php
$key = $this->registrar->register($todo);
```

Each registrar implementation:
1. Builds issue data from Todo (title, body, labels, assignees, etc.)
2. Calls external API to create issue
3. Returns issue key/number

| Registrar | Returns |
|---|---|
| JIRA | `PROJ-123` |
| GitHub | `#123` |
| GitLab | `#123` |

### Step 8: Inject Key into Comment

**Class:** `Dto\Comment\CommentPart`

After successful registration, inject the key back into the comment:

```php
$todo->injectKey($key); // Delegates to CommentPart.injectKey()
```

The injection position is configurable via `issueKeyInjection.position` setting. Three positions are supported:

**Position: `after_separator` (default)**

```php
// Before: TODO: Fix this bug
// After:  TODO: PROJ-123 Fix this bug
```

**Position: `before_separator`**

```php
// Before: TODO: Fix this bug
// After:  TODO PROJ-123: Fix this bug
```

**Position: `before_separator_sticky`**

```php
// Before: TODO: Fix this bug
// After:  TODO PROJ-123: Fix this bug
```

**Algorithm:**
1. Find separator offset in comment text (`:` or `-`)
2. Calculate injection offset based on configured position
3. Insert key at calculated offset with proper spacing
4. Add or replace separator if configured via `newSeparator` and `replaceSeparator`
5. Update first line of CommentPart

See [Issue Key Injection](../../Feature/IssueKeyInjection.md) for detailed configuration options.

### Step 9: Update Token Text

After key injection, update the original token:

```php
$token->text = $commentParts->getContent();
```

`CommentParts.getContent()` rebuilds the full comment text from all parts (both TODO and non-TODO parts).

### Step 10: Save File

**Class:** `Service\File\Saver`

Rebuild and save the file:

```php
$content = implode('', array_map(fn($token) => $token->text, $tokens));
file_put_contents($file->getPathname(), $content);
```

**Important:** File is saved immediately after each TODO registration, not batched. This ensures:
- Partial progress is preserved if process fails
- Each TODO gets registered and saved before moving to next

## Lazy Processing with Generators

The flow uses PHP Generators for memory-efficient processing:

```php
// HeapRunner.run()
foreach ($this->getTodos($statistic) as [$todo, $fileUpdateCallback]) {
    $this->register($todo);      // Register issue
    $fileUpdateCallback();       // Save file
}
```

**Generator Chain:**

```
run()
  └── getTodos()                     yields [Todo, callback]
        └── getCommentParts()        yields [CommentPart, callback]
              └── getFileHeaps()     yields FileHeap
                    └── finder       yields SplFileInfo
```

**Benefits:**
- Only one file loaded in memory at a time
- Immediate save after each registration
- Process can be interrupted and resumed (already registered TODOs are skipped)

## FileHeap and Update Callback

`FileHeap` encapsulates file processing context:

```php
$fileHeap = new FileHeap(
    $commentTokens,  // Filtered comment tokens
    $tokens,         // All tokens (for saving)
    $file,           // SplFileInfo
    $statistic,      // ProcessStatistic
    $saver,          // File saver
);
```

**fileUpdateCallback** is a closure that:
1. Increments registration counter
2. Updates statistics
3. Saves file with modified tokens

```php
$this->fileUpdateCallback = function () {
    ++$this->registrationCounter;
    $statistic->setFileRegistrationCount($file->getPathname(), $this->registrationCounter);
    $saver->save($file, $this->tokens);
};
```

## Error Handling

Errors during registration are wrapped in `CommentRegistrationException`:

```php
try {
    $key = $this->registrar->register($todo);
} catch (\Throwable $exception) {
    throw new CommentRegistrationException($todo, $exception);
}
```

This preserves context:
- Original exception
- Todo object with comment details
- Token with line number

## Statistics

`ProcessStatistic` tracks:
- Number of updated files
- Number of registered TODOs per file
- Total registered TODOs

```php
$statistic->getCountRegisteredTODOs();
$statistic->getCountUpdatedFiles();
```
