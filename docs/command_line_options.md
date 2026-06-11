## Command Line Options

First of all, pay attention to **available options:**

| Long Form | Short Form | Description |
|---|---|---|
| `--config=/path/to/config` | `-c /path/to/config` | Path to [configuration file](config/general_config.md) when it is not in default place |
| `--dry-run` | | Parse and count TODOs without API calls or source file changes. See [Dry-run mode](dry_run.md) |
| `--report-format=FORMAT` | | Export format for [processing report](report.md): `none`, `json`, `yaml` (default: `none`) |
| `--report-path=PATH` | | Report file path. Default: `todo-registrar-report.<format>`. Use `-` for stdout |
| | `-q`, `-v`, `-vv`, `-vvv` | Verbosity levels. The command uses [Symfony Console verbosity levels](https://symfony.com/doc/7.4/console/verbosity.html) |

**NOTE:** You can pass `--config=STDIN` so the [script loads YAML from STDIN](config/general_config_yaml.md#loading-from-stdin).
