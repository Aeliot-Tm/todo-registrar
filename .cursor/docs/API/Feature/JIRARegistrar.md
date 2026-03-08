# JIRA Registrar

## Overview

JIRA Registrar creates issues in Atlassian JIRA from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Creates JIRA issue with configured project key, type, and fields
3. Registers issue links if specified in inline config
4. Returns issue key (e.g., `PROJ-123`) which is injected back into comment

## Issue Fields Mapping

| TODO Comment | JIRA Issue Field |
|---|---|
| Summary (first line) | `summary` |
| Description (full text) | `description` |
| Tag assignee (`TODO@username`) | `assignee` |
| Inline config `assignee` | `assignee` |
| Config `issue.assignee` | `assignee` |
| Inline config `labels` | `labels[]` |
| Config `issue.labels` | `labels[]` |
| Tag name (if `addTagToLabels=true`) | `labels[]` |
| Inline config `components` | `components[]` |
| Config `issue.components` | `components[]` |
| Inline config `priority` | `priority` |
| Config `issue.priority` | `priority` |
| Inline config `issueType` | `issuetype` |
| Config `issue.issueType` | `issuetype` |
| Inline config `linkedIssues` | Issue links |

## Configuration

See [user documentation](../../../../docs/registrar/JIRA/config.md) for full configuration reference (YAML/PHP, authentication, inline config keys).

### Registrar type

```yaml
registrar:
  type: JIRA
```

### Service Configuration

```yaml
registrar:
  type: JIRA
  options:
    service:
      host: '%env(JIRA_HOST)%'
      personalAccessToken: '%env(JIRA_PERSONAL_ACCESS_TOKEN)%'
      tokenBasedAuth: true
      ## Alternative: username/password auth (tokenBasedAuth: false)
      # jiraUser: 'username'
      # jiraPassword: 'password'
```

### Issue Configuration

```yaml
registrar:
  options:
    issue:
      projectKey: 'PROJ'                  # Required: JIRA project key
      issueType: 'Task'                   # Required: issue type (Task, Bug, Story, etc.)
      priority: 'Medium'                  # Optional: default priority
      assignee: 'developer1'              # Optional: default assignee
      labels: ['tech-debt']               # Optional: default labels
      addTagToLabels: true                # Optional: add tag as label
      tagPrefix: 'tag-'                   # Optional: prefix for tag label
      allowedLabels: ['bug', 'feature']   # Optional: restrict allowed labels
      components: ['Backend']             # Optional: default components
      summaryPrefix: '[TODO] '            # Optional: prefix for issue summary
      showContext: 'numbered'             # Optional: include code context
      contextTitle: null                  # Optional: title of context path
      issueLinkType: null                 # Optional: default link type (default: 'Relates')
```

> **Note:** `issueType` replaces the deprecated `type` key. If both are specified, a validation error is raised.

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax.

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` | JIRA username to assign |
| `assignees` | `string` | Same as `assignee` |
| `components` | `string[]` | List of JIRA components |
| `contextTitle` | `string` | Title of context path |
| `issueType` | `string` | Issue type (Bug, Task, Story, etc.) |
| `labels` | `string[]` | List of labels to add |
| `linkedIssues` | `array` | Issue links (see [JIRA Linked Issues](JIRALinkedIssues.md)) |
| `priority` | `string` | Priority name |
| `projectKey` | `string` | Override project key |
| `showContext` | `string` | Override context display format |

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Tag assignee** — `TODO@username`
2. **Inline config** — `{EXTRAS: {assignee: user1}}`
3. **General config** — `issue.assignee` in config file

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `JiraRegistrar` | Main registrar, orchestrates issue creation |
| `JiraRegistrarFactory` | Creates registrar from config |
| `IssueFieldFactory` | Builds IssueField from Todo |
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `ServiceFactory` | Creates JIRA API service clients |
| `IssueServiceArrayConfigPreparer` | Prepares service config array |
| `IssueLinkRegistrar` | Creates issue links after issue creation |
| `LinkedIssueNormalizer` | Normalizes linked issues format |
| `IssueLinkTypeProvider` | Provides available link types from JIRA |
| `NotSupportedLinkTypeException` | Exception for unsupported link types |

### Source Path

`src/Service/Registrar/JIRA/`

### API Library

Uses `lesstif/php-jira-rest-client` library for JIRA API communication.

## Related Features

- [Allowed Labels](AllowedLabels.md) — filter labels applied to issues
- [Context Display](ContextDisplay.md) — show code context in issue description
- [Dynamic Summary Prefix](DynamicSummaryPrefix.md) — add prefixes to issue titles
- [Inline Configuration](InlineConfiguration.md) — per-comment overrides
- [JIRA Linked Issues](JIRALinkedIssues.md) — link new issues to existing ones
