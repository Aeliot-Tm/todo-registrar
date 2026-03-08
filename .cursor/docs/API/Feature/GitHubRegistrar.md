# GitHub Registrar

## Overview

GitHub Registrar creates issues in GitHub Issues from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Creates issue with title (summary) and body (description)
3. Registers missing labels in the repository if needed
4. Assigns users specified in config or inline
5. Returns issue number in format `#123` which is injected back into comment

## Issue Fields Mapping

| TODO Comment | GitHub Issue Field |
|---|---|
| Summary (first line) | `title` |
| Description (full text) | `body` |
| Tag assignee (`TODO@username`) | `assignees[]` |
| Inline config `assignees` | `assignees[]` |
| Config `issue.assignees` | `assignees[]` |
| Inline config `labels` | `labels[]` |
| Config `issue.labels` | `labels[]` |
| Tag name (if `addTagToLabels=true`) | `labels[]` |

## Configuration

See [user documentation](../../../../docs/registrar/GitHub/config.md) for full configuration reference (YAML/PHP, authentication, inline config keys).

### Registrar type

```yaml
registrar:
  type: GitHub
```

### Service Configuration

```yaml
registrar:
  options:
    service:
      personalAccessToken: '%env(GITHUB_PERSONAL_ACCESS_TOKEN)%'
      owner: 'username'                   # GitHub username or organization
      repository: 'repo-name'             # Repository name. May be used 'composite' ('username/repo-name') repo name then `owner` have to be omitted.
```

### Issue Configuration

```yaml
registrar:
  options:
    issue:
      assignees: ['developer1']          # Optional: default assignees
      labels: ['tech-debt']             # Optional: default labels
      addTagToLabels: true              # Optional: add tag as label
      tagPrefix: 'tag-'                 # Optional: prefix for tag label
      allowedLabels: ['bug', 'feature'] # Optional: restrict allowed labels
      summaryPrefix: '[TODO] '          # Optional: prefix for issue title
      showContext: 'numbered'           # Optional: include code context
      contextTitle: null                # Optional: title of context path
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax:

```php
// TODO: Fix this bug
//       {EXTRAS: {labels: [bug, urgent], assignees: [developer1, developer2]}}
```

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` | GitHub username to assign |
| `assignees` | `string[]` | List of GitHub usernames to assign |
| `contextTitle` | `string` | Title of context path |
| `labels` | `string[]` | List of labels to add |
| `owner` | `string` | GitHub username or organization |
| `repository` | `string` | Repository name |
| `showContext` | `string` | Override context display format |

## Label Auto-Creation

If labels specified in config or inline config don't exist in the repository, they are automatically created before the issue is created.

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Tag assignee** — `TODO@username`
2. **Inline config** — `{EXTRAS: {assignees: [user1]}}`
3. **General config** — `issue.assignees` in config file

## Example

### Comment in Code

```php
/**
 * TODO@john: Refactor this method
 *            The current implementation is too complex
 *            {EXTRAS: {labels: [refactoring, priority-low]}}
 */
function complexMethod() {
    // ...
}
```

### Created GitHub Issue

- **Title**: `Refactor this method`
- **Body**: `The current implementation is too complex`
- **Assignees**: `john`
- **Labels**: `refactoring`, `priority-low`, `todo` (if `addTagToLabels=true`)

### Result in Code

```php
/**
 * TODO: #42 Refactor this method
 *       The current implementation is too complex
 *       {EXTRAS: {labels: [refactoring, priority-low]}}
 */
```

**Note:** The position where the issue key is injected can be configured globally. See [Issue Key Injection](IssueKeyInjection.md) for details.

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `GitHubRegistrar` | Main registrar, orchestrates issue creation |
| `GitHubRegistrarFactory` | Creates registrar from config |
| `IssueFactory` | Builds Issue DTO from Todo |
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `Issue` | DTO for GitHub issue data |
| `ApiClientFactory` | Creates API clients |
| `IssueApiClient` | Wrapper for GitHub Issues API |
| `LabelApiClient` | Wrapper for GitHub Labels API |

### Source Path

`src/Service/Registrar/GitHub/`

### API Library

Uses `knplabs/github-api` library for GitHub API communication.

## Related Features

- [Allowed Labels](AllowedLabels.md) — filter labels applied to issues
- [Context Display](ContextDisplay.md) — show code context in issue description
- [Dynamic Summary Prefix](DynamicSummaryPrefix.md) — add prefixes to issue titles
- [Inline Configuration](InlineConfiguration.md) — per-comment overrides
