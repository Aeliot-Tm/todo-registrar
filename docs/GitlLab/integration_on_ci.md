# Integration on GitLab CI

### Preconditions

Config CI/CD variables:
- `CI_JOB_TOKEN` (set by GitLab CI) or `GITLAB_CONTROL_TOKEN` — token for Git operations and GitLab API (create MR).
  In Settings → General → Visibility, enable "Allow CI job token to create merge requests" if using `CI_JOB_TOKEN`.
- `GITLAB_PERSONAL_ACCESS_TOKEN` and `GITLAB_PROJECT_IDENTIFIER` — for todo-registrar to create issues in GitLab
  or others according to used issue tracker in TODO Registrar config.

### Configuration of GitLab CI pipeline

You have to describe pipeline in `.gitlab-ci.yml`. See [example](../../examples/GitLab/.gitlab-ci.yml).
The example uses [gitlab-control](https://gitlab.com/aeliot-tm/gitlab-control) and runs todo-registrar via `docker run` with project directory mounted to `/code`.
The runner must have Docker, git and curl.

> For the duties of example it added into stage `tests`. Usually it is quite good place.

**Algorithm of script:**
1. Download gitlab-control script and generate name for new branch (`todo-registrar-{random:8}`).
2. Check if previous MR is opened yet. Stop working when "yes".
3. Checkout new branch.
4. Run detection of not managed TODOs and creation of issues via todo-registrar.
5. Commit injected IDs of created issues.
6. Push changes and create Merge Request when something is committed.
