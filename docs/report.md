# Processing Report

The script collects information about the processing and can export it to a report file. Use this to integrate with CI pipelines, build custom dashboards, or archive run results.

## Options

| Option | Description |
|---|---|
| `--report-format=FORMAT` | Export format: `none`, `json`, `yaml`. Default: `none` |
| `--report-path=PATH` | Output file path. Default: `todo-registrar-report.<format>`. Use `-` for stdout |

When `--report-format` is `none` (default), no report file is generated.

## Usage examples

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

The report contains summary statistics and per-file details.

**Summary:**
- `files.analyzed` — number of analyzed files
- `files.updated` — number of files with registered TODOs
- `comments.detected` — total detected comment tokens
- `todos.ignored` — TODOs ignored (e.g. already had issue key)
- `todos.glued` — TODOs merged via sequential comments gluing
- `todos.registered` — newly registered TODOs
- `todos.total` — total TODOs (registered + glued + ignored)

**Files:** list of all analyzed files, each with path and `todos.registered` count (zero for files with no new registrations).

## JSON example

```json
{
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
      "registered": 7,
      "total": 12
    }
  },
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
summary:
  files:
    analyzed: 42
    updated: 5
  comments:
    detected: 15
  todos:
    ignored: 3
    glued: 2
    registered: 7
    total: 12
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
