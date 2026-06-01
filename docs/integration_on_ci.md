## Integration on CI

The main idea is monitoring of new TODOs on single branch of repository
to avoid creation of duplicated issues by competing processes and avoid merge conflicts.

The branch should be quite stable (without development directly in it. At the same time,
it should be as close to development as possible for earlier catching of tech-debt.
Soon of all, it is called `development`, but `main`/`master` is useful too.

Configure you integration depending on used git-server:

1. GitHub Action (use [TODO Registrar Action](https://github.com/marketplace/actions/todo-registrar))
2. [Configure GitLab CI](GitlLab/integration_on_ci.md)
