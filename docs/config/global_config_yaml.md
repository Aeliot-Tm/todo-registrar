# Configuration YAML file

> **Important:** YAML configuration files do not support automatic environment variable substitution.
> Unlike PHP configuration files (where you can use `$_ENV['VAR'] ?? null`), you must manually replace
> values in YAML files or use external tools/scripts to substitute environment variables before parsing.
> If you need dynamic configuration with environment variables, consider using PHP configuration format instead.
> See issue [#203](https://github.com/Aeliot-Tm/todo-registrar/issues/203).

It may have such structure:
```yaml
paths:                            # Optional. Defines paths which will be walked to find supported files
  in: /app                        # Optional. Accepts string (path) or array of strings (paths) to directories which
                                  #           will be scanned to find *.php files. It uses path to directory
                                  #           where the script is called when this option is omitted.
  append: bin/todo-registrar      # Optional. Accepts string (path) or array of strings (paths). It is a set of files
                                  #           which cannot be detected while scanning of directories.
                                  #           For example, shell scripts.
  exclude:                        # Optional. Accepts string (path) or array of strings (paths). It is a set of files
                                  #           which should be skipped when they are detected while the scanning
                                  #           of directories.
    - tests/fixtures
    - var

registrar:                        # Required. Configuration of Registrar
  type: GitHub                    # Required. Type of supported issue tracker or fully qualified class of custom factory
                                  #           of Registrar.
  options:                        # Required. Options necessary for exact Registrar.
    issue:
      labels: tech-debt
    service:
      personalAccessToken: a-token
      owner: am-i
      repository: am-i/a-repo

tags:           # Optional. Accepts string (tag) or array of strings (tags) which should be processed by the script.
  - my_tag
```
