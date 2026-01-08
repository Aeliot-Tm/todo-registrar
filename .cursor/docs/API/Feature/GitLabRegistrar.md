# GitLab Registrar

## Overview

GitLab Registrar creates issues in GitLab Issues from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Resolves usernames/emails to GitLab user IDs
3. Validates milestone if specified
4. Registers missing labels in the project if needed
5. Creates issue with all configured fields
6. Returns issue IID in format `#123` which is injected back into comment

## Issue Fields Mapping

| TODO Comment | GitLab Issue Field |
|---|---|
| Summary (first line) | `title` |
| Description (full text) | `description` |
| Tag assignee (`TODO@username`) | `assignee_ids[]` |
| Inline config `assignee` | `assignee_ids[]` |
| Config `issue.assignee` | `assignee_ids[]` |
| Inline config `labels` | `labels` |
| Config `issue.labels` | `labels` |
| Tag name (if `addTagToLabels=true`) | `labels` |
| Inline config `milestone` | `milestone_id` |
| Config `issue.milestone` | `milestone_id` |
| Inline config `due_date` | `due_date` |
| Config `issue.due_date` | `due_date` |

## Configuration

### Service Configuration

```yaml
registrar:
  type: GitLab
  service:
    personalAccessToken: 'glpat-xxx...'  # GitLab Personal Access Token
    # OR
    oauthToken: 'oauth-token'             # OAuth 2.0 token

    host: 'https://gitlab.com'            # GitLab host (optional, default: gitlab.com)
    project: 123                          # Project ID (integer)
    # OR
    project: 'owner/repo'                 # Project path (string)
```

### Issue Configuration

```yaml
registrar:
  issue:
    addTagToLabels: true         # Add TODO/FIXME tag as label
    tagPrefix: 'tag-'            # Prefix for tag label (e.g., "tag-todo")
    labels:                      # Default labels for all issues
      - tech-debt
      - from-code
    assignee:                    # Default assignees (usernames or emails)
      - developer1
      - developer@example.com
    milestone: 5                 # Default milestone (ID, IID, or title)
    due_date: '2025-12-31'       # Default due date (YYYY-MM-DD)
    summaryPrefix: '[TODO] '     # Prefix for issue title
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax:

```php
// TODO: Fix this bug
//       {EXTRAS: {labels: [bug, urgent], assignee: [dev1, dev2], milestone: Sprint_1}}
```

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` or `string[]` | Username(s) or email(s) to assign |
| `labels` | `string[]` | List of labels to add |
| `milestone` | `int` or `string` | Milestone ID, IID, or title |
| `due_date` | `string` | Due date in format `YYYY-MM-DD` |

## Features

### User Resolution

Usernames and emails are automatically resolved to GitLab user IDs via the Users API. Results are cached for performance.

### Milestone Resolution

Milestones can be specified by:
- **ID** (integer) — Direct milestone ID
- **IID** (integer) — Project-specific milestone number
- **Title** (string) — Milestone title (searched via API)

### Label Auto-Creation

Missing labels are automatically created at project level before issue creation.

### Self-Hosted GitLab

Supports self-hosted GitLab instances by specifying custom `host`:

```yaml
service:
  host: 'https://gitlab.mycompany.com'
  personalAccessToken: 'token'
  project: 'group/subgroup/project'
```

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignee: user1}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignee` in config file

## Example

### Comment in Code

```php
/**
 * TODO@john: Implement caching
 *            Add Redis caching for API responses
 *            {EXTRAS: {labels: [enhancement], milestone: Sprint_2, due_date: 2025-03-01}}
 */
function fetchData() {
    // ...
}
```

### Created GitLab Issue

- **Title**: `Implement caching`
- **Description**: `Add Redis caching for API responses`
- **Assignees**: `john` (resolved to user ID)
- **Labels**: `enhancement`, `todo` (if `addTagToLabels=true`)
- **Milestone**: Sprint_2 (resolved to milestone ID)
- **Due Date**: 2025-03-01

### Result in Code

```php
/**
 * TODO: #42 Implement caching
 *       Add Redis caching for API responses
 *       {EXTRAS: {labels: [enhancement], milestone: Sprint_2, due_date: 2025-03-01}}
 */
```

## Authentication Methods

### Personal Access Token (Recommended)

```yaml
service:
  personalAccessToken: 'glpat-xxx...'
```

Required scopes: `api` (for full API access)

### OAuth Token

```yaml
service:
  oauthToken: 'oauth-token-here'
```

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `GitlabRegistrar` | Main registrar, orchestrates issue creation |
| `GitlabRegistrarFactory` | Creates registrar from config |
| `IssueFactory` | Builds Issue DTO from Todo |
| `IssueConfig` | Holds parsed issue configuration |
| `Issue` | DTO for GitLab issue data |
| `ApiClientProvider` | Provides API client instances |
| `IssueApiClient` | Wrapper for GitLab Issues API |
| `LabelApiClient` | Wrapper for GitLab Labels API |
| `MilestoneApiClient` | Wrapper for GitLab Milestones API |
| `UserResolver` | Resolves usernames/emails to user IDs |

### API Library

Uses `m4tthumphrey/php-gitlab-api` library for GitLab API communication.
