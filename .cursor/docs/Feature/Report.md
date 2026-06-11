# Report

Exports processing statistics after a run. Configured via CLI options only (not in YAML/PHP config).

## What It Does

1. `HeapRunner` collects `ProcessStatistic` during file processing
2. `FileProcessor` records each inserted issue key via `ProcessStatistic::tickIssueKeyUsage()`
3. After registration completes, `RegisterCommand` optionally formats and writes the report
4. Default console summary is always printed; report file is optional

## CLI Options

| Option | Default | Description |
|---|---|---|
| `--dry-run` | off | Parse and count TODOs without API calls or file changes. See [DryRun Registrar](../Feature/DryRunRegistrar.md) |
| `--report-format` | `none` | `none`, `json`, or `yaml` |
| `--report-path` | `todo-registrar-report.<format>` | Output path; use `-` for stdout |

## Report Structure

```yaml
meta:
  version: 4.0.0      # Application::VERSION
  dryRun: false       # from --dry-run CLI flag
summary:
  files:
    analyzed: 10      # files visited
    updated: 3        # files with at least one registration
  comments:
    detected: 45      # comment tokens seen
  todos:
    ignored: 5        # skipped (existing key in tag line)
    glued: 2          # same-ticket gluing reuses
    newIssues: 6      # registered - glued (new tracker issues)
    registered: 8     # comments that received a key (new or reused)
    total: 15         # registered + glued + ignored
issues:
  - key: PROJ-1
    usageCounter: 3   # injected 3 times (e.g. 1 new + 2 glued)
  - key: PROJ-2
    usageCounter: 1
files:
  - path: src/Foo.php
    summary:
      todos:
        registered: 2
```

**Issues list:** sorted by `key`. Sum of `usageCounter` equals `summary.todos.registered`. Ignored TODOs (pre-existing
keys in comments) are not listed.

## Technical Details

| Class | Path |
|---|---|
| Builder | `src/Service/Report/ReportBuilder.php` |
| Dry-run registrar | `src/Service/Registrar/DryRun/DryRunRegistrar.php` |
| Dry-run factory | `src/Service/Registrar/DryRun/DryRunRegistrarFactory.php` |
| Format enum | `src/Enum/ReportFormat.php` |
| Run metadata | `src/Dto/ProcessMeta.php` |
| Statistics DTO | `src/Dto/ProcessStatistic.php`, `FileStatistic.php` |
| CLI wiring | `src/Console/Command/RegisterCommand.php` |
| Key usage tracking | `src/Service/FileProcessor.php` (`tickIssueKeyUsage` after each injection) |
| Dry-run flag in meta | `src/Service/HeapContextFactory.php` (`ProcessMeta::setDryRun`) |
