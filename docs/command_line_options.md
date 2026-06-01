## Command Line Options

First of all, pay attention to **available options:**

| Long Form | Short From | Description |
|---|---|---|
| `--config=/path/to/config` | `-c /path/to/config` | Path to [configuration file](config/general_config.md) when it is not in default place |
| `--report-format=FORMAT` | | Export format for [processing report](report.md): `none`, `json`, `yaml` (default: `none`) |
| `--report-path=PATH` | | Report file path. Default: `todo-registrar-report.<format>`. Use `-` for stdout |
| | `-q`, `-v`, `-vv`, `-vvv` | Verbosity levels. The command uses [Symfony Console verbosity levels](https://symfony.com/doc/7.4/console/verbosity.html) |

**NOTE:** You can pass a special value`--config=STDIN` then [script obtains YAML from STDIN](config/general_config_yaml.md#loading-from-stdin).
