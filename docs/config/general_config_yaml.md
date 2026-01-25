# Configuration YAML file

> There is described yaml-form of [general config file](general_config.md).

It can be loaded both from file and from STDIN.

## Structure of config

Generally, it has structure:

```yaml
paths:                            # Required. Defines paths which will be walked to find supported files
  in: /app                        # Required. Accepts string (path) or array of strings (paths) to directories which
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
                                  #           of Registrar (`App\MyTodoRegistrarFactory`).
  options:                        # Required. Options necessary for exact Registrar.
    issue:
      labels: tech-debt
    service:
      personalAccessToken: a-token
      owner: am-i
      repository: am-i/a-repo

tags:                             # Optional. Accepts string (tag) or array of strings (tags) which should be processed by the script.
  - my_tag
```

### Registrar options

Registrar options specific for each issue tracker see in separate documentation:

1. [GitHub](../registrar/GitHub/config.md)
2. [GitLab](../registrar/GitLab/config.md)
3. [JIRA](../registrar/JIRA/config.md)
4. [Redmine](../registrar/Redmine/config.md)
5. [Yandex Tracker](../registrar/YandexTracker/config.md)

### Loading from STDIN

You can pass YAML configuration via STDIN using `--config=STDIN` option:

```bash
# Pipe from file
cat .todo-registrar.yaml | php todo-registrar.phar --config=STDIN

# Stdin redirection
php todo-registrar.phar --config=STDIN < .todo-registrar.yaml

# Heredoc
php todo-registrar.phar --config=STDIN << 'EOF'
paths:
  in: /app/src
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: your-token
      owner: your-org
      repository: your-repo
EOF
```

#### Docker usage

When running in Docker, use the `-t` flag with `docker run` to disable TTY allocation,
which is required for STDIN to work properly:

```bash
# Pipe configuration file
cat .todo-registrar.yaml | docker run --rm -i -v $(pwd):/code ghcr.io/aeliot-tm/todo-registrar:latest --config=STDIN

# Stdin redirection
docker run --rm -i -v $(pwd):/code ghcr.io/aeliot-tm/todo-registrar:latest --config=STDIN < .todo-registrar.yaml

# Heredoc with environment variable substitution by shell
docker run --rm -i -v $(pwd):/code ghcr.io/aeliot-tm/todo-registrar:latest --config=STDIN << EOF
paths:
  in: /app/src
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: your-token
      owner: your-org
      repository: your-repo
EOF
```

## Environment Variables

YAML configuration files support environment variable substitution using the Symfony-style syntax
through the use of the project [aeliot/env-resolver](https://github.com/Aeliot-Tm/env-resolver).
Use `%env(VARIABLE_NAME)%` to reference environment variables.

```yaml
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: '%env(GITHUB_TOKEN)%'
      owner: '%env(GITHUB_OWNER)%'
      repository: '%env(GITHUB_REPO)%'
```

### Resolution Order

1. `$_ENV['VARIABLE_NAME']` — checked first
2. `getenv('VARIABLE_NAME')` — used as fallback

### Missing Variables

If an environment variable is not defined, script will be finished with error.

## Docker and Environment Variables

When using `%env(VAR)%` syntax in YAML configuration with Docker, you need to ensure that environment
variables are available inside the container.

### Passing Variables Securely

#### Method 1: Using `-e` flag (for single runs)

```bash
docker run --rm -it -e GITHUB_TOKEN="$GITHUB_TOKEN" -v $(pwd):/code ghcr.io/aeliot-tm/todo-registrar:latest
```

> **Security note:** Avoid passing the token value directly in the command (e.g., `-e GITHUB_TOKEN=ghp_xxx`).
> Always use shell variable expansion (`"$GITHUB_TOKEN"`) to prevent the secret from appearing in shell history.

#### Method 2: Using env_file (recommended for CI/CD)

Create a `.env` file (never commit to VCS):

```bash
GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx
GITHUB_OWNER=your-org
GITHUB_REPO=your-repo
```

Reference it in `compose.yaml`:

```yaml
services:
  php-cli:
    env_file:
      - .env
```

Or use directly with `docker run`:

```bash
docker run --rm --env-file .env -v "$(pwd):/app" ghcr.io/aeliot-tm/todo-registrar:latest
```

> **Tip:** This is the most secure method for `docker run` as secrets never appear in command arguments.

### Security Best Practices

1. **Never commit secrets to VCS** — add `.env` and files with tokens to `.gitignore`

2. **Use CI/CD secrets** — in GitHub Actions, GitLab CI, or other CI systems, use their built-in secret management:

   ```yaml
   # GitHub Actions example
   - name: Register TODOs
     env:
       GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
     run: docker run --rm -it -e GITHUB_TOKEN="$GITHUB_TOKEN" -v $(pwd):/code ghcr.io/aeliot-tm/todo-registrar:latest
   ```

3. **Prefer env_file over command-line** — passing secrets via `-e` flag may expose them in process listings

4. **Use read-only tokens** — create tokens with minimal required permissions (e.g., `repo` scope for GitHub)
