# Redmine Registrar

## Overview

Redmine Registrar creates issues in Redmine from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Resolves username/login to user ID if assignee is specified
3. Creates Redmine issue with configured project ID, tracker, and fields
4. Returns issue ID in format `#123` which is injected back into comment

## Issue Fields Mapping

| TODO Comment | Redmine Issue Field |
|---|---|
| Summary (first line) | `subject` |
| Description (full text) | `description` |
| Tag assignee (`TODO@username`) | `assigned_to_id` |
| Inline config `assignee` | `assigned_to_id` |
| Config `issue.assignee` | `assigned_to_id` |
| Inline config `tracker` | `tracker_id` |
| Config `issue.tracker` | `tracker_id` |
| Inline config `priority` | `priority_id` |
| Config `issue.priority` | `priority_id` |
| Inline config `category` | `category_id` |
| Config `issue.category` | `category_id` |
| Inline config `fixed_version` | `fixed_version_id` |
| Config `issue.fixed_version` | `fixed_version_id` |
| Inline config `start_date` | `start_date` |
| Config `issue.start_date` | `start_date` |
| Inline config `due_date` | `due_date` |
| Config `issue.due_date` | `due_date` |
| Inline config `estimated_hours` | `estimated_hours` |
| Config `issue.estimated_hours` | `estimated_hours` |

Redmine does not support labels. Options `labels`, `addTagToLabels`, `tagPrefix`, `allowedLabels` are accepted for compatibility but ignored.

## Configuration

See [user documentation](../../../../docs/registrar/Redmine/config.md) for full configuration reference (YAML/PHP, authentication, entity resolution, inline config keys).

### Registrar type

```yaml
registrar:
  type: Redmine
```

### Service Configuration

```yaml
registrar:
  options:
    service:
      url: 'https://redmine.example.com'
      apikeyOrUsername: '%env(REDMINE_USERNAME)%'
      password: '%env(REDMINE_PASSWORD)%'          # Optional: for Basic Auth
```

### Issue Configuration

```yaml
  options:
    issue:
      project: 'testing-project'        # Required: project identifier or ID
      tracker: 'Bugs'                   # Required: tracker name or ID
      priority: 'Low'                   # Optional: priority name or ID
      assignee: null                    # Optional: username, login, email, or user ID
      category: null                    # Optional: category name or ID
      fixed_version: null               # Optional: version name or ID
      start_date: null                  # Optional: YYYY-MM-DD
      due_date: null                    # Optional: YYYY-MM-DD
      estimated_hours: null             # Optional: estimated hours as float
      summaryPrefix: '[TODO] '          # Optional: prefix for issue subject
      showContext: 'numbered'           # Optional: include code context
      contextTitle: null                # Optional: title of context path
```

## Inline Configuration

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` or `int` | Username/login or user ID to assign |
| `assignees` | `string` or `int` | Same as `assignee` |
| `category` | `int` or `string` | Category ID or name |
| `contextTitle` | `string` | Title of context path |
| `due_date` | `string` | Due date in format `YYYY-MM-DD` |
| `estimated_hours` | `float` | Estimated hours |
| `fixed_version` | `int` or `string` | Version ID or name |
| `priority` | `int` or `string` | Priority ID or name |
| `project` | `int` or `string` | Project identifier or ID |
| `showContext` | `string` | Override context display format |
| `start_date` | `string` | Start date in format `YYYY-MM-DD` |
| `tracker` | `int` or `string` | Tracker ID or name |

## Features

### User Resolution

Usernames, logins, and emails are automatically resolved to Redmine user IDs via the Users API. Results are cached. User IDs can also be specified directly.

### Entity Resolution

Tracker, priority, category, and version can be specified by ID (integer) or name (string). Names are resolved to IDs via Redmine API (cached).

- **Tracker**: Resolved via `/trackers.xml`
- **Priority**: Resolved via `/enumerations/issue_priorities.xml`
- **Category**: Resolved via `/projects/{id}/issue_categories.xml`
- **Version**: Resolved via `/projects/{id}/versions.xml`

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Tag assignee** — `TODO@username`
2. **Inline config** — `{EXTRAS: {assignee: user1}}`
3. **General config** — `issue.assignee` in config file

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `RedmineRegistrar` | Main registrar, orchestrates issue creation |
| `RedmineRegistrarFactory` | Creates registrar from config |
| `IssueFactory` | Builds Issue DTO from Todo |
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `ServiceFactory` | Creates Redmine API client |
| `IssueApiClient` | Wrapper for Redmine Issues API |
| `UserResolver` | Resolves usernames/logins to user IDs |
| `EntityResolver` | Resolves tracker/priority/category/version names to IDs |
| `Issue` | DTO for Redmine issue data |
| `ProjectNotFoundException` | Exception when project not found |

### Source Path

`src/Service/Registrar/Redmine/`

### API Library

Uses `kbsali/redmine-api` library for Redmine API communication.

## Related Features

- [Context Display](ContextDisplay.md) — show code context in issue description
- [Dynamic Summary Prefix](DynamicSummaryPrefix.md) — add prefixes to issue titles
- [Inline Configuration](InlineConfiguration.md) — per-comment overrides
