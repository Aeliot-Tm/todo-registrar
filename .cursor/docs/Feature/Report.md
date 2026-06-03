# Report

Exports processing statistics after a run. Configured via CLI options only (not in YAML/PHP config).

## What It Does

1. `HeapRunner` collects `ProcessStatistic` during file processing
2. After registration completes, `RegisterCommand` optionally formats and writes the report
3. Default console summary is always printed; report file is optional

## CLI Options

| Option | Default | Description |
|---|---|---|
| `--report-format` | `none` | `none`, `json`, or `yaml` |
| `--report-path` | `todo-registrar-report.<format>` | Output path; use `-` for stdout |

## Report Structure

```yaml
summary:
  files:
    analyzed: 10      # files visited
    updated: 3        # files with at least one registration
  comments:
    detected: 45      # comment tokens seen
  todos:
    ignored: 5        # skipped (existing key in tag line)
    glued: 2          # same-ticket gluing reuses
    registered: 8     # new API registrations
    total: 15         # registered + glued + ignored
files:
  - path: src/Foo.php
    summary:
      todos:
        registered: 2
```

## Technical Details

| Class | Path |
|---|---|
| Builder | `src/Service/Report/ReportBuilder.php` |
| Format enum | `src/Enum/ReportFormat.php` |
| Statistics DTO | `src/Dto/ProcessStatistic.php`, `FileStatistic.php` |
| CLI wiring | `src/Console/Command/RegisterCommand.php` |
