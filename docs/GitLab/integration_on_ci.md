# Integration on GitLab CI

### Preconditions

Config CI/CD variables:
- `CI_JOB_TOKEN` (set by GitLab CI) or `GITLAB_CONTROL_TOKEN` — token for Git operations and GitLab API (create MR).
  In Settings → General → Visibility, enable "Allow CI job token to create merge requests" if using `CI_JOB_TOKEN`.
- `GITLAB_PERSONAL_ACCESS_TOKEN` and `GITLAB_PROJECT_IDENTIFIER` — for todo-registrar to create issues in GitLab
  or others according to used issue tracker in TODO Registrar config.

The runner must have Docker, git, curl and `jq` (or install `jq` in `before_script` as in the example).

### Configuration of GitLab CI pipeline

You have to describe pipeline in `.gitlab-ci.yml`. See example of [.gitlab-ci.yml](../../examples/GitLab/.gitlab-ci.yml).
The example uses [gitlab-control](https://gitlab.com/aeliot-tm/gitlab-control)
and runs todo-registrar via `docker run` with project directory mounted to `/code`.

### Algorithm of script:
1. Download gitlab-control script and generate name for new branch (`todo-registrar-{random:8}`).
2. Check if previous MR is opened yet. Stop working when "yes".
3. Checkout new branch.
4. Run detection of not managed TODOs and creation of issues via todo-registrar.
   Export JSON report to `/runner-temp` to build useful description.
5. Commit injected IDs of created issues (only source changes under `/code`).
6. Push changes.
7. _Optional._ Build MR description with [build-mr-body.sh](../../bin/build-mr-body.sh).
8. Create Merge Request when something is committed.

> For the duties of example it added into stage `tests`. Usually it is quite good place.

> Download [build-mr-body.sh](../../bin/build-mr-body.sh) via `curl` in `before_script`
> or place it next to your config (`./scripts/build-mr-body.sh`).

### CI artifacts isolation (`/runner-temp`)

`gitlab-control commit` runs `git add -A .`. To avoid committing the processing report or MR description,
store CI artifacts outside the mounted `/code` directory:

```bash
export RUNNER_TEMP="/tmp/todo-registrar-${CI_PIPELINE_ID}"
mkdir -p "$RUNNER_TEMP"
```

Mount it into the container and write the report there:

```bash
docker run --rm \
    -v "$(pwd):/code" \
    -v "${RUNNER_TEMP}:/runner-temp" \
    ... \
    --report-format=json --report-path=/runner-temp/todo-registrar-report.json
```

After commit and push, build the MR description from the report:

```bash
REPORT_PATH="${RUNNER_TEMP}/todo-registrar-report.json"
MR_BODY_PATH="${RUNNER_TEMP}/mr-body.md"
./scripts/build-mr-body.sh "$REPORT_PATH" "$MR_BODY_PATH"
./gitlab-control create_mr \
    --title "TODO-REGISTRAR: automated registering of new TODOs" \
    --description "$(cat "$MR_BODY_PATH")"
```

### MR statistic (dry-run)

On merge request pipelines, scan the branch for unregistered TODOs and post a sticky summary note on the MR.
Use [post-mr-statistic-comment.sh](../../bin/post-mr-statistic-comment.sh) — it builds the comment and calls
[gitlab-control](https://gitlab.com/aeliot-tm/gitlab-control) `upsert_mr_note` without exposing implementation details.

Download scripts in `before_script` and set `GITLAB_CONTROL` to a fixed path:

```bash
export GITLAB_CONTROL="${CI_PROJECT_DIR}/.ci/gitlab-control"
export POST_MR_STATISTIC_SCRIPT="${CI_PROJECT_DIR}/.ci/post-mr-statistic-comment.sh"
mkdir -p "$(dirname "$GITLAB_CONTROL")"
curl -sL "https://gitlab.com/aeliot-tm/gitlab-control/-/raw/main/controller.sh" -o "$GITLAB_CONTROL"
chmod +x "$GITLAB_CONTROL"
curl -sL "https://raw.githubusercontent.com/Aeliot-Tm/todo-registrar/refs/heads/main/bin/post-mr-statistic-comment.sh" \
  -o "$POST_MR_STATISTIC_SCRIPT"
chmod +x "$POST_MR_STATISTIC_SCRIPT"
```

Run todo-registrar in dry-run mode and post the comment:

```bash
export RUNNER_TEMP="/tmp/todo-registrar-statistic-${CI_PIPELINE_ID}"
mkdir -p "$RUNNER_TEMP"

docker run --rm \
    -v "$(pwd):/code" \
    -v "${RUNNER_TEMP}:/runner-temp" \
    ghcr.io/aeliot-tm/todo-registrar:latest \
    --dry-run \
    --config=STDIN \
    --report-format=json \
    --report-path=/runner-temp/todo-registrar-report.json \
    <<'EOF'
paths:
  in: /code
registrar:
  type: DryRun
EOF

"$POST_MR_STATISTIC_SCRIPT" "${RUNNER_TEMP}/todo-registrar-report.json"
```

Job rules example: `if: $CI_PIPELINE_SOURCE == "merge_request_event"`.

For `upsert_mr_note`, the job token needs permission to add notes to merge requests
(Project Access Token with `api` scope via `GITLAB_CONTROL_TOKEN` is a reliable alternative).
