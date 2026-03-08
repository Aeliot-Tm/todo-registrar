# Yandex Tracker Registrar

## Overview

Yandex Tracker Registrar creates issues in Yandex Tracker from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Creates Yandex Tracker issue with configured queue, type, and fields
3. Returns issue key (e.g., `QUEUE-123`) which is injected back into comment

## Issue Fields Mapping

| TODO Comment | Yandex Tracker Issue Field |
|---|---|
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
| Inline config `issueType` | `type` |
| Config `issue.type` | `type` |

## Configuration

See [user documentation](../../../../docs/registrar/YandexTracker/config.md) for full configuration reference (YAML/PHP, authentication, environment variables, inline config keys).

### Registrar type

```yaml
registrar:
  type: YandexTracker
```

### Service Configuration

```yaml
registrar:
  type: YandexTracker
  options:
    service:
      orgId: '%env(YANDEX_TRACKER_ORG_ID)%'     # Required
      token: '%env(YANDEX_TRACKER_TOKEN)%'      # Required
      isCloud: true                             # Optional: Cloud vs on-premise
```

### Issue Configuration

```yaml
registrar:
    issue:
      queue: MYQUEUE                              # Required: queue key
      type: task                                  # Required: issue type
      priority: normal                            # Optional: priority
      assignee: developer.login                   # Optional: default assignee
      labels: [tech-debt]                         # Optional: default labels
      addTagToLabels: true                        # Optional: add tag as label
      tagPrefix: 'tag-'                           # Optional: prefix for tag label
      allowedLabels: [tech-debt, another-label]   # Optional: restrict allowed labels
      summaryPrefix: '[TODO] '                    # Optional: prefix for issue summary
      showContext: 'numbered'                     # Optional: include code context
      contextTitle: null                          # Optional: title of context path
```

## Inline Configuration

### Supported Inline Config Keys

| Key | Type | Description |
|---|---|---|
| `assignee` | `string` | User login to assign |
| `assignees` | `string` | Same as `assignee` |
| `contextTitle` | `string` | Title of context path |
| `issueType` | `string` | Issue type (task, bug, story, epic, etc.) |
| `labels` | `string[]` | List of tags to add |
| `priority` | `string` | Priority name (blocker, critical, normal, minor, trivial) |
| `queue` | `string` | Queue key |
| `showContext` | `string` | Override context display format |

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Tag assignee** — `TODO@username`
2. **Inline config** — `{EXTRAS: {assignee: user1}}`
3. **General config** — `issue.assignee` in config file

## Authentication

### OAuth Token

Uses OAuth token with `tracker:write` scope.

### Organization ID

Found in Yandex Tracker settings: https://tracker.yandex.com/admin/orgs
Or via API: `GET https://api.tracker.yandex.net/v2/myself`

### Cloud Organization

If `isCloud: true` (default), `X-Cloud-Org-ID` header is used instead of `X-Org-ID`.

![UI for Organization ID](../../../../docs/registrar/YandexTracker/ui_org_id.png)

## Technical Details

### Key Classes

| Class | Responsibility |
|---|---|
| `YandexTrackerRegistrar` | Main registrar, orchestrates issue creation |
| `YandexTrackerRegistrarFactory` | Creates registrar from config |
| `IssueFactory` | Builds issue request from Todo |
| `GeneralIssueConfig` | Holds parsed issue configuration |
| `ApiClientFactory` | Creates Yandex Tracker API client |
| `ExtendedIssueCreateRequest` | Extended SDK request with tags support |

### Source Path

`src/Service/Registrar/YandexTracker/`

### API Library

Uses `bugrov/yandex-tracker` (from GitHub `Aeliot-Tm/yandex-tracker`) library for Yandex Tracker API communication.

## Related Features

- [Allowed Labels](AllowedLabels.md) — filter labels applied to issues
- [Context Display](ContextDisplay.md) — show code context in issue description
- [Dynamic Summary Prefix](DynamicSummaryPrefix.md) — add prefixes to issue titles
- [Inline Configuration](InlineConfiguration.md) — per-comment overrides
