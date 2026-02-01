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

### Service Configuration

```yaml
registrar:
  type: GitHub
  service:
    personalAccessToken: 'ghp_xxx...'  # GitHub Personal Access Token
    owner: 'username'                   # GitHub username or organization
    repository: 'repo-name'             # Repository name
```

### Issue Configuration

```yaml
registrar:
  issue:
    addTagToLabels: true      # Add TODO/FIXME tag as label
    tagPrefix: 'tag-'         # Prefix for tag label (e.g., "tag-todo")
    labels:                   # Default labels for all issues
      - tech-debt
      - from-code
    assignees:                # Default assignees
      - developer1
    summaryPrefix: '[TODO] '  # Prefix for issue title
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
| `assignees` | `string[]` | List of GitHub usernames to assign |
| `labels` | `string[]` | List of labels to add |

## Label Auto-Creation

If labels specified in config or inline config don't exist in the repository, they are automatically created before the issue is created.

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignees: [user1]}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignees` in config file

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
| `IssueConfig` | Holds parsed issue configuration |
| `Issue` | DTO for GitHub issue data |
| `ApiClientFactory` | Creates API clients |
| `IssueApiClient` | Wrapper for GitHub Issues API |
| `LabelApiClient` | Wrapper for GitHub Labels API |

### API Library

Uses `knplabs/github-api` library for GitHub API communication.
