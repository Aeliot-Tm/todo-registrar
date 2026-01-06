# Configuration YAML file

## Environment Variables

YAML configuration files support environment variable substitution using the Symfony-style syntax:

```yaml
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: '%env(GITHUB_TOKEN)%'
      owner: '%env(GITHUB_OWNER)%'
      repository: '%env(GITHUB_REPO)%'
```

### Syntax

Use `%env(VARIABLE_NAME)%` to reference environment variables. The value must be the **entire string** — 
partial substitution like `prefix_%env(VAR)%_suffix` is not supported.

### Resolution Order

1. `$_ENV['VARIABLE_NAME']` — checked first
2. `getenv('VARIABLE_NAME')` — used as fallback

### Missing Variables

If an environment variable is not defined, the placeholder `%env(VARIABLE_NAME)%` remains unchanged in the configuration. This allows you to detect configuration errors early.

## Loading from file

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

## Loading from STDIN

You can pass YAML configuration via STDIN using `--config=STDIN` option:

```bash
# Pipe from file
cat .todo-registrar.yaml | ./bin/todo-registrar register --config=STDIN

# Stdin redirection
./bin/todo-registrar register --config=STDIN < .todo-registrar.yaml

# Heredoc
./bin/todo-registrar register --config=STDIN << 'EOF'
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

### Docker usage

When running in Docker, use the `-T` flag with `docker compose exec` to disable TTY allocation,
which is required for STDIN to work properly:

```bash
# Pipe configuration file
cat .todo-registrar.yaml | docker compose exec -T php-cli ./bin/todo-registrar register --config=STDIN

# Stdin redirection
docker compose exec -T php-cli ./bin/todo-registrar register --config=STDIN < .todo-registrar.yaml

# Heredoc with environment variable substitution by shell
docker compose exec -T php-cli ./bin/todo-registrar register --config=STDIN << EOF
paths:
  in: /app/src
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: ${GITHUB_TOKEN}
      owner: ${GITHUB_OWNER}
      repository: ${GITHUB_REPO}
EOF
```

> **Note:** In the heredoc example above, environment variables like `${GITHUB_TOKEN}` are substituted
> by the shell **before** the YAML is passed to the application. Use unquoted `EOF` to enable variable
> substitution, or `'EOF'` (quoted) to pass the literal `${VAR}` strings without substitution.

## Docker and Environment Variables

When using `%env(VAR)%` syntax in YAML configuration with Docker, you need to ensure that environment 
variables are available inside the container.

### Passing Variables Securely

#### Method 1: Using `-e` flag (for single runs)

```bash
# With docker compose exec
docker compose exec -e GITHUB_TOKEN="$GITHUB_TOKEN" php-cli ./bin/todo-registrar register

# With docker run
docker run --rm -e GITHUB_TOKEN="$GITHUB_TOKEN" -v "$(pwd):/app" todo-registrar ./bin/todo-registrar register
```

> **Security note:** Avoid passing the token value directly in the command (e.g., `-e GITHUB_TOKEN=ghp_xxx`).
> Always use shell variable expansion (`"$GITHUB_TOKEN"`) to prevent the secret from appearing in shell history.

#### Method 2: Using `environment` in compose.yaml (recommended for development)

```yaml
services:
  php-cli:
    environment:
      - GITHUB_TOKEN=${GITHUB_TOKEN}
      - GITHUB_OWNER=${GITHUB_OWNER}
      - GITHUB_REPO=${GITHUB_REPO}
```

Then in `.todo-registrar.yaml`:

```yaml
registrar:
  type: GitHub
  options:
    service:
      personalAccessToken: '%env(GITHUB_TOKEN)%'
      owner: '%env(GITHUB_OWNER)%'
      repository: '%env(GITHUB_REPO)%'
```

#### Method 3: Using env_file (recommended for CI/CD)

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
docker run --rm --env-file .env -v "$(pwd):/app" todo-registrar ./bin/todo-registrar register
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
     run: docker compose exec -T php-cli ./bin/todo-registrar register
   ```

3. **Prefer env_file over command-line** — passing secrets via `-e` flag may expose them in process listings

4. **Use read-only tokens** — create tokens with minimal required permissions (e.g., `repo` scope for GitHub)
