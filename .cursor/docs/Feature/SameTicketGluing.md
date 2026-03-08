# Same-Ticket Gluing

Links identical TODOs to the same issue instead of creating duplicate tickets.

## What It Does

1. Before creating an issue, computes a normalized hash of the TODO's content (tag, assignee, summary, description including inline config)
2. If a TODO with the same hash was already processed in the current run, reuses the same issue key
3. Injects the reused key into all matching TODO comments

## Configuration

Configured via `process.glueSameTickets` option. Default: `false` (disabled).

## When to Use

- The same TODO text appears in multiple places in the codebase
- You want to avoid creating duplicate tickets for repeated identical TODOs

## How Identity Is Determined

Two TODOs are considered identical when their tag, assignee, summary, and description (including inline config) produce the same normalized hash. Normalization strips redundant whitespace (including line breaks) before comparison. Comparison is done within a single run — TODOs that already have an injected key are skipped.

## Key Source Paths

- Gluing logic: `src/Service/HeapRunner.php`
- Config: `src/Dto/GeneralConfig/ProcessConfig.php`
