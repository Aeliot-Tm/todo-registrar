# Processing Report

The script collects information about the processing and can export it to a report file.
Use this to integrate with CI pipelines, build custom dashboards, or archive run results.

## Options

| Option | Description |
|---|---|
| `--dry-run` | Parse and count TODOs without API calls or source file changes. See [Dry-run mode](dry_run.md) |
| `--report-format=FORMAT` | Export format: `none`, `json`, `yaml`. Default: `none` |
| `--report-path=PATH` | Output file path. Default: `todo-registrar-report.<format>`. Use `-` for stdout |

When `--report-format` is `none` (default), no report file is generated.

## Usage examples

**Dry-run with JSON report (for CI statistics):**
```shell
todo-registrar register --dry-run --report-format=json --report-path=-
```

**Export to JSON file (default path `todo-registrar-report.json`):**
```shell
todo-registrar --report-format=json
```

**Export to custom path:**
```shell
todo-registrar --report-format=yaml --report-path=./reports/run-$(date +%Y%m%d).yaml
```

**Output report to stdout:**
```shell
todo-registrar -q --report-format=json --report-path=-
```

## Report structure

The report contains run metadata, summary statistics, a list of inserted issue keys, and per-file details.

### Meta

Run context for archived reports and CI artifacts:

- `meta.version` — application version that produced the report
- `meta.dryRun` — whether the run used `--dry-run` (no API calls and no source file changes)

### Summary

- `summary.files.analyzed` — number of analyzed files
- `summary.files.updated` — number of files with at least one registration
- `summary.comments.detected` — total detected comment tokens
- `summary.todos.ignored` — TODOs skipped because the comment already contained an issue key
- `summary.todos.glued` — TODOs that reused an issue key via same-ticket gluing in this run
- `summary.todos.newIssues` — new issues that would be created in the tracker (`registered - glued`)
- `summary.todos.registered` — TODO comments that received an issue key in this run (new or reused)
- `summary.todos.total` — total TODOs (`registered + glued + ignored`)

### Issues

List of issue keys inserted during the run, sorted alphabetically by `key`. Each entry contains:

- `key` — issue key returned by the registrar (or a dry-run placeholder such as `#dry-run-1`)
- `usageCounter` — how many times this key was injected into TODO comments in this run

TODOs that were ignored because they already had a key are **not** included in `issues`.

When same-ticket gluing reuses a key, `usageCounter` for that key is greater than `1`. The sum of all
`usageCounter` values equals `summary.todos.registered`.

### Files

List of all analyzed files. Each entry contains:

- `path` — file path
- `summary.todos.registered` — number of TODO comments that received a key in this file (zero when the file had no
  registrations)

## JSON example

```json
{
  "meta": {
    "version": "4.0.0",
    "dryRun": true
  },
  "summary": {
    "files": {
      "analyzed": 42,
      "updated": 5
    },
    "comments": {
      "detected": 15
    },
    "todos": {
      "ignored": 3,
      "glued": 2,
      "newIssues": 5,
      "registered": 7,
      "total": 12
    }
  },
  "issues": [
    {
      "key": "PROJ-10",
      "usageCounter": 1
    },
    {
      "key": "PROJ-11",
      "usageCounter": 3
    },
    {
      "key": "PROJ-12",
      "usageCounter": 3
    }
  ],
  "files": [
    {
      "path": "src/Service/Foo.php",
      "summary": {
        "todos": {
          "registered": 3
        }
      }
    },
    {
      "path": "src/Service/Bar.php",
      "summary": {
        "todos": {
          "registered": 0
        }
      }
    }
  ]
}
```

## YAML example

```yaml
meta:
  version: 4.0.0
  dryRun: true
summary:
  files:
    analyzed: 42
    updated: 5
  comments:
    detected: 15
  todos:
    ignored: 3
    glued: 2
    newIssues: 5
    registered: 7
    total: 12
issues:
  - key: PROJ-10
    usageCounter: 1
  - key: PROJ-11
    usageCounter: 3
  - key: PROJ-12
    usageCounter: 3
files:
  - path: src/Service/Foo.php
    summary:
      todos:
        registered: 3
  - path: src/Service/Bar.php
    summary:
      todos:
        registered: 0
```
