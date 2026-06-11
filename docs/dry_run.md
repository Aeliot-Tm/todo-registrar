# Dry-run mode

Dry-run lets you scan source files, count TODOs, and export a [processing report](report.md) without calling an issue
tracker API or changing source files. Use it on CI to estimate how many new issues a run would create, or locally to
preview scope before registration.

## Two ways to enable dry-run

| Mechanism | What it does |
|---|---|
| `--dry-run` CLI flag | Enables dry-run **run mode**: no API calls, no file changes, statistics only. Always uses the `DryRun` registrar, regardless of `registrar.type` in config. |
| `registrar.type: DryRun` in config | Selects a registrar that returns fake keys (`#dry-run-1`, `#dry-run-2`, …) and needs no tracker credentials or `options`. Does **not** by itself skip file updates — combine with `--dry-run` for CI. |

For CI statistics, pass **`--dry-run`** and use either your normal config or a minimal config with `type: DryRun`.

## CLI option

| Option | Description |
|---|---|
| `--dry-run` | Parse and count TODOs without API calls or source file changes |

Example:

```shell
todo-registrar register --dry-run --report-format=json --report-path=-
```

Console summary uses **Would register** instead of **Registered**. The exported report sets `meta.dryRun` to `true`
and lists dry-run placeholder keys (for example `#dry-run-1`) in `issues`. See [Processing report](report.md) for report
options and structure.

## Minimal config with DryRun registrar

When dry-run runs often (for example on every pull request), a dedicated config avoids storing tracker tokens and
service options in the CI environment:

```yaml
paths:
  in: src

registrar:
  type: DryRun
```

`registrar.options` can be omitted — the `DryRun` registrar ignores it.

You can keep the same `paths`, `tags`, and `process` sections as in your production config; only the `registrar`
block is simplified.

### PHP config

```php
$config->setRegistrar(RegistrarType::DryRun, []);
```

## Behavior during dry-run

When `--dry-run` is set:

1. Parsing, TODO extraction, same-ticket gluing, and statistics work as in a normal run.
2. `DryRunRegistrar` assigns sequential fake keys (`#dry-run-1`, …) instead of calling the tracker.
3. Issue keys are **not** injected into comments and source files are **not** saved.
4. Per-file and run-level statistics (including `newIssues` in the report) reflect what would be registered.

See [Processing Flow](processing_flow.md) for the full pipeline.

## Related documentation

- [Processing report](report.md) — JSON/YAML export for CI
- [Command line options](command_line_options.md) — all CLI flags
- [Configuration](configuration.md) — general config and registrar types
