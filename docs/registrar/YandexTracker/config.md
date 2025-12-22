# Configuration of YandexTracker-registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/YandexTracker/.todo-registrar.php).

Description of keys of general config:
```php
$yandexTrackerConfig = [
    'queue' => 'MYQUEUE',                           // key of Yandex Tracker queue (required)
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue tags or not
        'assignee' => 'string',                     // login of Yandex Tracker user, which will be assigned to issue
                                                    // when "assignee-suffix" was not used with tag.
        'labels' => ['a-label'],                    // list of tags which will be set to issue
        'priority' => 'normal',                     // priority of issue (blocker, critical, normal, minor, trivial)
        'summaryPrefix' => '[TODO] ',               // prefix which will be added to issue summary
        'tagPrefix' => 'tag-',                      // prefix which will be added to tag when "addTagToLabels=true"
        'type' => 'task',                           // type of issue (task, bug, story, epic, etc.)
    ],
    'service' => [
        'cloudOrgId' => 'string'                    // Cloud Organization ID (X-Cloud-Org-ID header)
        // 'orgId' => 'string',                     // Organization ID (X-Org-ID header)
        'token' => 'string',                        // OAuth token for Yandex Tracker API
    ]
];
```

## YAML configuration

See [example](../../../examples/YandexTracker/.todo-registrar.yaml).

```yaml
registrar:
  type: YandexTracker
  queue: MYQUEUE
  issue:
    addTagToLabels: true
    assignee: developer.login
    labels:
      - tech-debt
      - from-code
    priority: normal
    tagPrefix: ''
    type: task
  service:
    cloudOrgId: '%env(YANDEX_TRACKER_CLOUD_ORG_ID)%'
    # orgId: '%env(YANDEX_TRACKER_ORG_ID)%'
    token: '%env(YANDEX_TRACKER_TOKEN)%'
```

## Inline config

Supported keys of inline config:

| Key        | Description                                                                                                              |
|------------|--------------------------------------------------------------------------------------------------------------------------|
| assignee   | Login of Yandex Tracker user as string, which will be assigned to the issue. This one will be used when it is defined.   |
| issue_type | Type of issue (task, bug, story, epic, etc.).                                                                            |
| labels     | List of tags which will be assigned to the issue.                                                                        |
| priority   | Priority of issue as string (blocker, critical, normal, minor, trivial).                                                 |

### Example of inline config

```php
// TODO: Fix authentication bug
//       {EXTRAS: {issue_type: bug, priority: critical, labels: [security, urgent]}}
```

## Authentication

### Getting OAuth Token

1. Go to [Yandex OAuth](https://oauth.yandex.com/) and register your application
2. Request token with `tracker:write` scope
3. Follow link: `https://oauth.yandex.com/authorize?response_type=token&client_id=<client_id>`
4. Authorize the application
5. Use the received token in configuration

For more details, see [Yandex Tracker API Access](https://yandex.com/support/tracker/concepts/access.html).

### Getting Organization ID

The organization ID can be found in:
- Yandex Tracker settings page: https://tracker.yandex.com/admin/orgs
- Or via API call: `GET https://api.tracker.yandex.net/v2/myself`

Pay attention if it 'Cloud' organization then you have to provide `orgCloudId` in configuration instead of `orgId`

![img.png](img.png)

## Environment Variables

For security, use environment variables for sensitive data:

```yaml
service:
  token: '%env(YANDEX_TRACKER_TOKEN)%'
  orgId: '%env(YANDEX_TRACKER_ORG_ID)%'
```

Set environment variables before running:
```bash
export YANDEX_TRACKER_TOKEN="y0_AgAAAABXXXXXXXXXXXXXXXXXXXXXXXXX"
export YANDEX_TRACKER_ORG_ID="123456"
```

Or use `.env` file with Docker:
```bash
# .env
YANDEX_TRACKER_TOKEN=y0_AgAAAABXXXXXXXXXXXXXXXXXXXXXXXXX
YANDEX_TRACKER_ORG_ID=123456
```
