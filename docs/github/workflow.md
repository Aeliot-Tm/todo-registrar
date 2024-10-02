# Integration on GitHub Actions

### Preconditions

Add token to the secrets of repository for connection to GitHub API with permission to write contents.

### Configuration of GitHub Actions

You have to config [workflow](https://docs.github.com/en/actions/writing-workflows) of your project.
See example [in this project](../../.github/workflows/todo-registrar.yaml).

It is split on two jobs:
- The first one gets count of opened Pull Request with new registered TODOs.
- The second one responsible for the whole process of registration of TODOs.
  It is skipped when opened PRs detected by previous job to avoid duplicated registration of TODOs.

**NOTE:**
Pay attention that workflow of this project depends on using of its own binary file.
But you have to [install](../../README.md#installation) TODO Registrar and use proper path to binary file.
