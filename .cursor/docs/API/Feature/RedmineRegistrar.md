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

## Configuration

### Service Configuration

```yaml
registrar:
  type: Redmine
  project: 1                            # Redmine project ID or literal identifier (required)
  service:
    url: 'https://redmine.example.com'  # Redmine URL
    apikeyOrUsername: 'your-api-key'    # API key (recommended) or username
    # If password is provided, Basic Auth will be used (username:password)
    # Otherwise, apikeyOrUsername will be treated as API key
    # password: 'password'               # Optional: for Basic Auth
```

### Issue Configuration

```yaml
registrar:
  issue:
    tracker: 1                     # Default tracker (ID or name: 'Bug', 'Feature', etc.)
    assignee: null                  # Default assignee (username, login, email, or user ID) (optional)
    priority: 2                     # Default priority (ID or name: 'High', 'Normal', etc.) (optional)
    category: null                  # Default category (ID or name) (optional)
    fixed_version: null             # Default version (ID or name) (optional)
    start_date: null                # Default start date YYYY-MM-DD (optional)
    due_date: null                  # Default due date YYYY-MM-DD (optional)
    estimated_hours: null           # Default estimated hours (optional)
    addTagToLabels: false           # Redmine doesn't support labels directly
    tagPrefix: 'tag-'
    summaryPrefix: '[TODO] '        # Prefix for issue subject
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax:

```php
// TODO: Fix this bug
//       {EXTRAS: {tracker: Bug, priority: High, due_date: 2025-03-01}}
```

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` or `int` | Username/login or user ID to assign |
| `tracker` | `int` or `string` | Tracker ID or name (Bug, Feature, Support, etc.) |
| `priority` | `int` or `string` | Priority ID or name (High, Normal, Low, etc.) |
| `category` | `int` or `string` | Category ID or name |
| `fixed_version` | `int` or `string` | Version ID or name |
| `start_date` | `string` | Start date in format `YYYY-MM-DD` |
| `due_date` | `string` | Due date in format `YYYY-MM-DD` |
| `estimated_hours` | `float` | Estimated hours |

## Features

### User Resolution

Usernames, logins, and emails are automatically resolved to Redmine user IDs via the Users API. Results are cached for performance. User IDs can also be specified directly.

### Entity Resolution

Tracker, priority, category, and version can be specified by ID (integer) or name (string). Names are automatically resolved to IDs via Redmine API. Results are cached for performance.

- **Tracker**: Resolved via `/trackers.xml` endpoint
- **Priority**: Resolved via `/enumerations/issue_priorities.xml` endpoint
- **Category**: Resolved via `/projects/{id}/issue_categories.xml` endpoint
- **Version**: Resolved via `/projects/{id}/versions.xml` endpoint

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignee: user1}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignee` in config file

### Assignee Resolution

The `assignee` field can be specified as:
- Username (string)
- Login (string)
- Email (string)
- User ID (integer)

All string values are automatically resolved to user IDs via the Redmine Users API. Results are cached for performance.

## Example

### Comment in Code

```php
/**
 * TODO@john: Fix authentication bug
 *            Security vulnerability in login
 *            {EXTRAS: {tracker: Bug, priority: High, due_date: 2025-03-01}}
 */
function authenticate() {
    // ...
}
```

### Created Redmine Issue

- **Project ID**: 1 (from config)
- **Tracker**: Bug (resolved to tracker ID from inline config)
- **Subject**: `Fix authentication bug`
- **Description**: `Security vulnerability in login`
- **Assigned To**: john (resolved to user ID)
- **Priority**: High (resolved to priority ID from inline config)
- **Due Date**: 2025-03-01

### Result in Code

```php
/**
 * TODO: #42 Fix authentication bug
 *       Security vulnerability in login
 *       {EXTRAS: {tracker: Bug, priority: High, due_date: 2025-03-01}}
 */
```

## Authentication Methods

### API Key (Recommended)

```yaml
service:
  url: 'https://redmine.example.com'
  apikeyOrUsername: 'your-api-key'
```

Generate API key in Redmine: My account → API access key

### Username/Password

```yaml
service:
  url: 'https://redmine.example.com'
  apikeyOrUsername: 'username'
  password: 'password'
```

If `password` is provided, `apikeyOrUsername` is treated as username and Basic Auth is used. Otherwise, it's treated as API key.

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

### API Library

Uses `kbsali/redmine-api` library for Redmine API communication.

### Issue Creation

The `IssueFactory` creates an `Issue` DTO with all configured fields:

```php
$issue = new Issue();
$issue
    ->setSubject('Issue subject')
    ->setDescription('Issue description')
    ->setProjectId(1)
    ->setTrackerId(1)
    ->setAssignedToId(123)
    ->setPriorityId(2)
    ->setDueDate('2025-03-01');
```
