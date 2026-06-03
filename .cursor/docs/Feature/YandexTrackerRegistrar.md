# Yandex Tracker Registrar

Creates Yandex Tracker issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue request via `IssueFactory` and `IssueSupporter`
2. Maps config `labels` to Tracker `tags`
3. Creates issue via `bugrov/yandex-tracker` SDK
4. Returns issue `key` (e.g. `MYQUEUE-42`, without `#` prefix) for key injection

## Issue Fields Mapping

| Source | Tracker field |
|---|---|
| Summary (+ prefix) | `summary` |
| Description (+ context) | `description` |
| Assignee | `assignee` |
| Labels (+ tag label) | `tags[]` |
| Priority | `priority` |
| Inline `issueType` / config `type` | `type` |

## Configuration

```yaml
registrar:
  type: YandexTracker
  options:
    queue: MYQUEUE                 # fallback if not in issue section
    service:
      token: '%env(YANDEX_TRACKER_TOKEN)%'
      orgId: '%env(YANDEX_TRACKER_ORG_ID)%'
      isCloud: true                # true → X-Cloud-Org-ID header
    issue:
      queue: MYQUEUE               # required
      type: task                   # required
      assignee: null
      priority: null
      labels: []
      addTagToLabels: false
      tagPrefix: ''
      allowedLabels: []
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

Authentication: OAuth token with `tracker:write` scope.

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string |
| `labels` | string[] (mapped to tags) |
| `issueType` | string (alias `issue_type` also accepted) |
| `priority` | string |
| `queue` | string |
| `showContext`, `contextTitle` | string |

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/YandexTracker/YandexTrackerRegistrar.php` |
| Factory | `src/Service/Registrar/YandexTracker/YandexTrackerRegistrarFactory.php` |
| Issue builder | `src/Service/Registrar/YandexTracker/IssueFactory.php` |
| API factory | `src/Service/Registrar/YandexTracker/ApiClientFactory.php` |
| Request DTO | `src/Service/Registrar/YandexTracker/ExtendedIssueCreateRequest.php` |

Library: `bugrov/yandex-tracker`.

Related: [Allowed Labels](AllowedLabels.md), [Context Display](ContextDisplay.md), [Inline Configuration](InlineConfiguration.md).
