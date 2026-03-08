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

See [user documentation](../../../docs/registrar/GitLab/config.md) for full configuration reference (YAML/PHP, authentication, project identification, inline config keys).

### Registrar type

```yaml
registrar:
  type: GitLab
```

### Service Configuration

```yaml
registrar:
  options:
    service:
      host: 'https://gitlab.com'                                 # Optional
      personalAccessToken: '%env(GITLAB_PERSONAL_ACCESS_TOKEN)%' # OR oauthToken
```

### Issue Configuration

```yaml
registrar:
  options:
    issue:
      project: 123                         # Required: project ID or path
      assignee: ['username1']              # Optional: default assignees
      labels: ['tech-debt']                # Optional: default labels
      addTagToLabels: true                 # Optional: add tag as label
      tagPrefix: 'tag-'                    # Optional: prefix for tag label
      allowedLabels: ['bug', 'feature']    # Optional: restrict allowed labels
      milestone: 5                         # Optional: default milestone
      due_date: '2025-12-31'               # Optional: default due date
      summaryPrefix: '[TODO] '             # Optional: prefix for issue title
      showContext: 'numbered'              # Optional: include code context
      contextTitle: null                   # Optional: title of context path
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax.

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` or `string[]` | Username(s) or email(s) to assign |
| `assignees` | `string` or `string[]` | Same as `assignee` |
| `contextTitle` | `string` | Title of context path |
| `due_date` | `string` | Due date in format `YYYY-MM-DD` |
| `labels` | `string[]` | List of labels to add |
| `milestone` | `int` or `string` | Milestone ID or title |
| `project` | `int` or `string` | Project ID or path |
| `showContext` | `string` | Override context display format |

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

Supports self-hosted GitLab instances by specifying custom `host`.

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Tag assignee** — `TODO@username`
2. **Inline config** — `{EXTRAS: {assignee: user1}}`
3. **General config** — `issue.assignee` in config file

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
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `Issue` | DTO for GitLab issue data |
| `ApiClientFactory` | Creates API client |
| `ApiSectionClientFactory` | Creates API section clients |
| `IssueApiClient` | Wrapper for GitLab Issues API |
| `LabelApiClient` | Wrapper for GitLab Labels API |
| `MilestoneApiClient` | Wrapper for GitLab Milestones API |
| `UserResolver` | Resolves usernames/emails to user IDs |

### Source Path

`src/Service/Registrar/GitLab/`

### API Library

Uses `m4tthumphrey/php-gitlab-api` library for GitLab API communication.

## Related Features

- [Allowed Labels](AllowedLabels.md) — filter labels applied to issues
- [Context Display](ContextDisplay.md) — show code context in issue description
- [Dynamic Summary Prefix](DynamicSummaryPrefix.md) — add prefixes to issue titles
- [Inline Configuration](InlineConfiguration.md) — per-comment overrides
