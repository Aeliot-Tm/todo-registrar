# JIRA Registrar

Creates JIRA issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue fields via `IssueFieldFactory` and `IssueSupporter`
2. Creates issue via `IssueService::create()`
3. Registers issue links from inline `linkedIssues` if present
4. Returns issue key (e.g. `PROJ-42`) for key injection

## Issue Fields Mapping

| Source | JIRA field |
|---|---|
| Summary (+ prefix) | `summary` |
| Description (+ context) | `description` |
| Assignee | `assignee` |
| Labels | `labels[]` |
| Components | `components[]` |
| Priority | `priority` |
| Inline `issueType` / config `issueType` | `issuetype` |
| Inline `projectKey` | `project` key |
| Inline `linkedIssues` | issue links (after create) |

## Configuration

```yaml
registrar:
  type: JIRA
  options:
    projectKey: PROJ              # fallback if not in issue section
    issueLinkType: Relates       # default link type for linkedIssues list form
    service:
      host: '%env(JIRA_HOST)%'
      personalAccessToken: '%env(JIRA_TOKEN)%'
      tokenBasedAuth: true
      # Alternative: jiraUser + jiraPassword with tokenBasedAuth: false
    issue:
      projectKey: PROJ
      issueType: Task
      assignee: null
      priority: null
      components: []
      labels: []
      issueLinkType: null
      addTagToLabels: false
      tagPrefix: ''
      allowedLabels: []
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

**`issueType` vs `type`:** use `issueType`. Legacy `type` is migrated automatically; specifying both raises a validation error.

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string |
| `labels`, `components` | string[] |
| `issueType` | string |
| `priority` | string |
| `projectKey` | string |
| `linkedIssues` | list or map — see [JIRA Linked Issues](JIRALinkedIssues.md) |
| `showContext`, `contextTitle` | string |

Legacy inline key `issue_type` is also accepted in `IssueFieldFactory`.

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/JIRA/JiraRegistrar.php` |
| Factory | `src/Service/Registrar/JIRA/JiraRegistrarFactory.php` |
| Field factory | `src/Service/Registrar/JIRA/IssueFieldFactory.php` |
| Service factory | `src/Service/Registrar/JIRA/ServiceFactory.php` |
| Issue config | `src/Service/Registrar/JIRA/GeneralIssueConfig.php` |

Library: `lesstif/php-jira-rest-client`.

Related: [JIRA Linked Issues](JIRALinkedIssues.md), [Allowed Labels](AllowedLabels.md), [Context Display](ContextDisplay.md), [Inline Configuration](InlineConfiguration.md).
