# Report

Exports processing results to a report file for integration with CI pipelines, dashboards, or archiving.

## What It Does

1. Collects statistics during processing (files analyzed, TODOs detected, registered, etc.)
2. Exports the collected data in the selected format (JSON or YAML)
3. Supports output to file or stdout

## CLI Options

| Option | Description |
|---|---|
| `--report-format=FORMAT` | Export format: `none` (default), `json`, `yaml` |
| `--report-path=PATH` | Output file path. Use `-` for stdout |

## Report Contents

- **Summary**: files analyzed/updated, comments detected, TODOs registered/ignored/glued
- **Files**: per-file breakdown with registration counts

See [user documentation](../../../../docs/report.md) for full structure, JSON/YAML examples, and usage commands.

## Key Source Paths

- Report builder: `src/Service/Report/ReportBuilder.php`
- Format enum: `src/Enum/ReportFormat.php`
