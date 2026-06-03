# Redmine Registrar

Creates Redmine issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue via `IssueFactory` and `IssueSupporter`
2. Resolves project, tracker, category, version, priority, assignee by name or ID
3. Creates issue via Redmine REST API (XML)
4. Returns `#<id>` for key injection

## Issue Fields Mapping

| Source | Redmine field |
|---|---|
| Summary (+ prefix) | `subject` |
| Description (+ context) | `description` |
| Assignee | `assigned_to_id` |
| Tracker | `tracker_id` (required) |
| Priority | `priority_id` |
| Category | `category_id` |
| Fixed version | `fixed_version_id` |
| Dates / hours | `start_date`, `due_date`, `estimated_hours` |

Redmine has no labels. Options `labels`, `addTagToLabels`, `tagPrefix`, `allowedLabels` are accepted in config for shared schema compatibility but not sent to the API.

## Configuration

```yaml
registrar:
  type: Redmine
  options:
    service:
      url: 'https://redmine.example.com'
      apikeyOrUsername: '%env(REDMINE_API_KEY)%'
      password: null               # for Basic Auth when using username
    issue:
      project: my-project          # required: identifier or numeric ID
      tracker: Bug                 # required: name or ID
      assignee: null
      priority: null
      category: null
      fixed_version: null
      start_date: null
      due_date: null
      estimated_hours: null
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string or int |
| `project`, `tracker`, `priority`, `category`, `fixed_version` | string or int |
| `start_date`, `due_date` | string (`YYYY-MM-DD`) |
| `estimated_hours` | float |
| `showContext`, `contextTitle` | string |

## Entity Resolution

Names resolved via API (cached): tracker (`/trackers.xml`), priority, category (per project), version (per project).
Usernames, logins, emails resolved via Users API (`UserResolver`).

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/Redmine/RedmineRegistrar.php` |
| Factory | `src/Service/Registrar/Redmine/RedmineRegistrarFactory.php` |
| Issue builder | `src/Service/Registrar/Redmine/IssueFactory.php` |
| Entity resolver | `src/Service/Registrar/Redmine/EntityResolver.php` |
| User resolver | `src/Service/Registrar/Redmine/UserResolver.php` |

Library: `kbsali/redmine-api`.

Related: [Context Display](ContextDisplay.md), [Dynamic Summary Prefix](DynamicSummaryPrefix.md), [Inline Configuration](InlineConfiguration.md).
