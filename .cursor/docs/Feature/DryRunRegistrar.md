# DryRun Registrar

Registrar that simulates issue creation without calling an external tracker. Returns sequential fake keys
`#dry-run-1`, `#dry-run-2`, …

Used in two ways:

1. **`--dry-run` CLI flag** — `HeapRunnerFactory` forces `RegistrarType::DryRun` and empty registrar config;
   `HeapContext::isDryRun` is true → no key injection, `FileHeap::recordRegistration()` instead of save.
2. **`registrar.type: DryRun` in config** — minimal config without tracker credentials; registrar behavior
   depends on whether `--dry-run` is also passed (see [Dry-run mode](../../../docs/dry_run.md)).

## Configuration

```yaml
registrar:
  type: DryRun
```

`options` is optional and ignored.

## CLI

| Option | Effect |
|---|---|
| `--dry-run` | Run mode: no API, no file writes; forces `DryRun` registrar |

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/DryRun/DryRunRegistrar.php` |
| Factory | `src/Service/Registrar/DryRun/DryRunRegistrarFactory.php` |
| Enum case | `src/Enum/RegistrarType.php` (`DryRun`) |
| Registrar selection | `src/Service/HeapRunnerFactory.php` (`getRegistrar()`) |
| Skip save / inject | `src/Service/FileProcessor.php` (`HeapContext::isDryRun`) |
| Statistics only save | `src/Dto/FileHeap.php` (`recordRegistration()`) |

`RegistrarProvider::getRegistrar()` accepts registrar type and config array separately so `--dry-run` can override
config without loading real tracker options.
