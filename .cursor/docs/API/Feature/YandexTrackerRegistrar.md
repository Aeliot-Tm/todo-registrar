# Yandex Tracker Registrar

## Overview

Yandex Tracker Registrar creates issues in Yandex Tracker from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Creates Yandex Tracker issue with configured queue, type, and fields
3. Returns issue key (e.g., `QUEUE-123`) which is injected back into comment

## Issue Fields Mapping

| TODO Comment | Yandex Tracker Issue Field |
|--------------|---------------------------|
| Summary (first line) | `summary` |
| Description (full text) | `description` |
| Tag assignee (`TODO@username`) | `assignee` |
| Inline config `assignee` | `assignee` |
| Config `issue.assignee` | `assignee` |
| Inline config `labels` | `tags[]` |
| Config `issue.labels` | `tags[]` |
| Tag name (if `addTagToLabels=true`) | `tags[]` |
| Inline config `priority` | `priority` |
| Config `issue.priority` | `priority` |
| Inline config `issue_type` | `type` |
| Config `issue.type` | `type` |

## Configuration

### Service Configuration

```yaml
registrar:
  type: YandexTracker
  queue: 'QUEUE'                        # Yandex Tracker queue key (required)
  service:
    token: 'OAuth-token'                # OAuth token for API access
    orgId: 'organization-id'            # Organization ID (X-Org-ID header)
```

### Issue Configuration

```yaml
registrar:
  issue:
    type: 'task'                 # Default issue type (task, bug, story, epic)
    addTagToLabels: true         # Add TODO/FIXME tag as label
    tagPrefix: 'tag-'            # Prefix for tag label (e.g., "tag-todo")
    labels:                      # Default labels (tags) for all issues
      - tech-debt
      - from-code
    assignee: 'developer1'       # Default assignee (login)
    priority: 'normal'           # Default priority (blocker, critical, normal, minor, trivial)
    summaryPrefix: '[TODO] '     # Prefix for issue summary
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax:

```php
// TODO: Fix this bug
//       {EXTRAS: {issue_type: bug, priority: critical, labels: [urgent]}}
```

### Supported Inline Config Keys

| Key | Type | Description |
|-----|------|-------------|
| `assignee` | `string` | User login to assign |
| `issue_type` | `string` | Issue type (task, bug, story, epic, etc.) |
| `priority` | `string` | Priority name (blocker, critical, normal, minor, trivial) |
| `labels` | `string[]` | List of tags to add |

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignee: user1}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignee` in config file

## Example

### Comment in Code

```php
/**
 * TODO@john: Refactor authentication module
 *            Current implementation has security issues
 *            {EXTRAS: {issue_type: bug, priority: critical}}
 */
function authenticate() {
    // ...
}
```

### Created Yandex Tracker Issue

- **Queue**: QUEUE
- **Type**: bug
- **Summary**: `Refactor authentication module`
- **Description**: `Current implementation has security issues`
- **Assignee**: john
- **Priority**: critical
- **Tags**: `todo` (if `addTagToLabels=true`)

### Result in Code

```php
/**
 * TODO: QUEUE-42 Refactor authentication module
 *       Current implementation has security issues
 *       {EXTRAS: {issue_type: bug, priority: critical}}
 */
```

## Authentication

### OAuth Token

To get an OAuth token for Yandex Tracker API:

1. Go to [Yandex OAuth](https://oauth.yandex.com/authorize?response_type=token&client_id=...)
2. Authorize the application
3. Copy the token

For more details, see [Yandex Tracker API Access](https://yandex.ru/support/tracker/concepts/access.html).

### Organization ID

The organization ID (`orgId`) can be found in:
- Yandex Tracker settings
- Or obtained via API call to get current user info

## Technical Details

### Key Classes

| Class | Responsibility |
|-------|----------------|
| `YandexTrackerRegistrar` | Main registrar, orchestrates issue creation |
| `YandexTrackerRegistrarFactory` | Creates registrar from config |
| `IssueFactory` | Builds issue request from Todo |
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `ApiClientFactory` | Creates Yandex Tracker API client |
| `ExtendedIssueCreateRequest` | Extended SDK request with tags support |

### API Library

Uses `bugrov/yandex-tracker` (from GitHub `intensa/yandex-tracker`) library for Yandex Tracker API communication.

### Issue Creation

The `IssueFactory` builds an `ExtendedIssueCreateRequest` using fluent API:

```php
$request = new ExtendedIssueCreateRequest();
$request
    ->queue('QUEUE')
    ->summary('Issue summary')
    ->description('Issue description')
    ->type('task')
    ->assignee('username')
    ->priority('normal')
    ->tags(['label1', 'label2']);

$response = $request->send();
$issueKey = $response->getField('key'); // e.g., "QUEUE-123"
```

## Limitations

- The SDK `bugrov/yandex-tracker` is not published on Packagist and is loaded from GitHub via VCS repository
- Issue linking is not yet implemented (can be added in future versions)
- Components and sprints are not yet supported

