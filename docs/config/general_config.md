# Configuration file

Configuration file can either in YAML format ([see documentation about config YAML-file](general_config_yaml))
or PHP format ([see documentation about config PHP-file](general_config_php)). You can define custom path
to config by option `--config=/custom/path/to/cofig`. When option `--config` is omitted then script tries to find
default config file in the root directory of project (exactly in directory from which the script was called).

The orders of files which are looked for:
1. `.todo-registrar.yaml`
2. `.todo-registrar.dist.yaml`
3. `.todo-registrar.php`
4. `.todo-registrar.dist.php`
5. `.todo-registrar.yml`
6. `.todo-registrar.dist.yml`

Otherwise, you may pass a special value`--config=STDIN` then [it obtains YAML from STDIN](general_config_yaml#loading-from-stdin).
