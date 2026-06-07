# GitHub Registrar

Creates GitHub Issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue title and body via `IssueSupporter` (summary prefix, context, labels)
2. Resolves owner/repository from inline config or `issue` config (supports `owner/repo` composite)
3. Creates missing labels via Labels API (`ColorGenerator` for new label colors)
4. Creates issue via Issues API
5. Returns `#<number>` for key injection

## Issue Fields Mapping

| Source | GitHub field |
|---|---|
| Summary (+ prefix) | `title` |
| Description (+ context) | `body` |
| Tag `@username`, inline/config assignees | `assignees[]` |
| Inline/config labels (+ tag label) | `labels[]` |
| Inline `owner`, `repository` | target repository |

## Configuration

```yaml
registrar:
  type: GitHub    # case-insensitive; alias 'github' also works
  options:
    service:
      personalAccessToken: '%env(GITHUB_TOKEN)%'
      owner: my-org              # optional fallback for issue.owner
      repository: my-repo        # optional; or 'my-org/my-repo' composite
    issue:
      owner: my-org              # required (or from service)
      repository: my-repo        # required; must not contain '/'
      assignees: []
      labels: []
      addTagToLabels: false
      tagPrefix: ''
      allowedLabels: []
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string / string[] |
| `labels` | string[] |
| `owner`, `repository` | string |
| `showContext`, `contextTitle` | string |

## Assignee Priority

Merge order in `IssueSupporter::getAssignees()`: tag assignee → inline → `issue.assignees`.

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/GitHub/GitHubRegistrar.php` |
| Factory | `src/Service/Registrar/GitHub/GitHubRegistrarFactory.php` |
| Issue builder | `src/Service/Registrar/GitHub/IssueFactory.php` |
| Issue config | `src/Service/Registrar/GitHub/GeneralIssueConfig.php` |
| API clients | `src/Service/Registrar/GitHub/ApiClientFactory.php` |
| Label API | `src/Service/Registrar/GitHub/LabelApiClient.php` |

Library: `knplabs/github-api`.

Related: [Allowed Labels](AllowedLabels.md), [Context Display](ContextDisplay.md), [Dynamic Summary Prefix](DynamicSummaryPrefix.md), [Inline Configuration](InlineConfiguration.md), [Issue Key Injection](IssueKeyInjection.md).
