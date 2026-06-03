# GitLab Registrar

Creates GitLab Issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue fields via `IssueSupporter`
2. Resolves assignee usernames/emails to user IDs (`UserResolver`, cached)
3. Validates milestone if specified (`MilestoneApiClient`)
4. Creates missing project labels (`LabelApiClient`)
5. Creates issue via Issues API
6. Returns `#<iid>` (project-internal ID) for key injection

## Issue Fields Mapping

| Source | GitLab field |
|---|---|
| Summary (+ prefix) | `title` |
| Description (+ context) | `description` |
| Assignees | `assignee_ids[]` |
| Labels | `labels` |
| Inline/config `milestone` | `milestone_id` |
| Inline/config `due_date` | `due_date` |
| Inline/config `project` | target project |

## Configuration

```yaml
registrar:
  type: GitLab
  options:
    service:
      host: 'https://gitlab.com'     # optional, default gitlab.com
      personalAccessToken: '%env(GITLAB_TOKEN)%'   # or oauthToken
      project: my-group/my-project   # optional fallback for issue.project
    issue:
      project: 123                   # required: ID or path
      assignee: []                   # note: singular key name
      labels: []
      milestone: null
      due_date: null
      addTagToLabels: false
      tagPrefix: ''
      allowedLabels: []
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

Authentication: `personalAccessToken` or `oauthToken` (scope `api`).

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string / string[] |
| `labels` | string[] |
| `milestone` | int or string (ID, IID, or title) |
| `due_date` | string (`YYYY-MM-DD`) |
| `project` | int or string |
| `showContext`, `contextTitle` | string |

## Milestone Resolution

By integer: treated as milestone ID or IID (validated via API).
By string: searched by title in the project.

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/GitLab/GitlabRegistrar.php` |
| Factory | `src/Service/Registrar/GitLab/GitlabRegistrarFactory.php` |
| Issue builder | `src/Service/Registrar/GitLab/IssueFactory.php` |
| User resolver | `src/Service/Registrar/GitLab/UserResolver.php` |
| Milestone API | `src/Service/Registrar/GitLab/MilestoneApiClient.php` |

Library: `m4tthumphrey/php-gitlab-api`.

Related: [Allowed Labels](AllowedLabels.md), [Context Display](ContextDisplay.md), [Dynamic Summary Prefix](DynamicSummaryPrefix.md), [Inline Configuration](InlineConfiguration.md).
