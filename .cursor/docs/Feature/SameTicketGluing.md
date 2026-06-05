# Same-Ticket Gluing

Reuses an already-created issue key for identical TODOs within one run instead of creating duplicate tickets.

## What It Does

1. `TodoBuilder` computes a hash (`crc32`) from tag, assignee, summary, and description
2. Before calling the registrar, `HeapRunner` checks `HeapContext.hashToKey` for the same hash in this run
3. If `process.glueSameTickets` is true and hash matches, skips API call, reuses stored key, increments glued counter
4. Injects the reused key into all matching comments

TODOs that already have a ticket key in the tag line are always skipped (ignored counter).

## Configuration

```yaml
process:
  glueSameTickets: false   # default
```

## Identity Rules

Hash input (whitespace-normalized):

- Tag name
- Assignee from tag metadata
- Summary (first line after tag)
- Full description (remaining lines, including `{EXTRAS: ...}` block)

Normalization: `trim(preg_replace('/\s+/u', ' ', $raw))` then `crc32()`.

Comparison scope: single CLI run only. Cross-run deduplication is not supported.

## Technical Details

| Class | Path |
|---|---|
| Gluing logic | `src/Service/HeapRunner.php` (`register()`) |
| Run-scoped hash map | `src/Dto/HeapContext.php` (`hashToKey`) |
| Hash calculation | `src/Service/TodoBuilder.php` (`calculateHash()`) |
| Config | `src/Dto/GeneralConfig/ProcessConfig.php` |
| Statistics | `src/Dto/ProcessStatistic.php` (`tickGluedTodo()`) |
