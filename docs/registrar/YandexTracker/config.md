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
        'allowedLabels' => ['tag-1', 'tag-2'],     // optional: list of allowed tags. If set, only tags from this
                                                    //           list will be applied to issues. Tags from inline
                                                    //           config, general config, and tag-based tags (if
                                                    //           addTagToLabels=true) will be filtered to match this list.
        'assignee' => 'string',                     // login of Yandex Tracker user, which will be assigned to issue
                                                    // when "assignee-suffix" was not used with tag.
        'labels' => ['a-label'],                    // list of tags which will be set to issue
        'priority' => 'normal',                     // priority of issue (blocker, critical, normal, minor, trivial)
        'summaryPrefix' => '[TODO] ',               // prefix which will be added to issue summary
        'tagPrefix' => 'tag-',                      // prefix which will be added to tag when "addTagToLabels=true"
        'type' => 'task',                           // type of issue (task, bug, story, epic, etc.)
    ],
    'service' => [
        'isCloud' => true,                          // Is Cloud Organization (default: true)
                                                    // If true, X-Cloud-Org-ID header is passed instead of X-Org-ID
        'orgId' => 'string',                        // Organization ID (required)
        'token' => 'string',                        // OAuth token for Yandex Tracker API (required)
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
    isCloud: '%env(YANDEX_TRACKER_IS_CLOUD)%'
    orgId: '%env(YANDEX_TRACKER_ORG_ID)%'
    token: '%env(YANDEX_TRACKER_TOKEN)%'
```

## Allowed Labels

The `allowedLabels` option allows you to restrict which tags can be applied to issues. This is useful when you want to ensure only predefined tags from your Yandex Tracker queue are used.

### How it works

When `allowedLabels` is set (non-empty array), the registrar filters all collected tags to keep only those that are present in the `allowedLabels` list. The collected tags come from:

1. Tags specified in inline config (via `{EXTRAS: {labels: [...]}}`)
2. Tags from general config (`labels` option)
3. Tag-based tag (if `addTagToLabels` is `true`, format: `{tagPrefix}{tag}`)

### Example

```php
'issue' => [
    'addTagToLabels' => true,
    'allowedLabels' => ['bug', 'feature', 'tech-debt'],
    'labels' => ['tech-debt'],
    'tagPrefix' => 'todo-',
]
```

With this configuration:
- If a TODO comment has `{EXTRAS: {labels: [bug, urgent]}}`, only `bug` will be applied (because `urgent` is not in `allowedLabels`)
- The general config tag `tech-debt` will be applied
- If the tag is `TODO`, the tag-based tag `todo-todo` will be filtered out (not in `allowedLabels`)

### When to use

- **Queue policy enforcement**: Ensure only approved tags are used
- **Prevent typos**: Avoid creating issues with misspelled tags
- **Tag management**: Control which tags can be created automatically

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

### Cloud Organization

If you are using Yandex Tracker Cloud organization, set `isCloud: true` in the service configuration.
When `isCloud` is `true`, the `X-Cloud-Org-ID` header is used instead of `X-Org-ID`.
By default, `isCloud` is `true`.

For on-premise installations, set `isCloud: false`.

![img.png](img.png)

## Environment Variables

For security, use environment variables for sensitive data:

```yaml
service:
  isCloud: '%env(YANDEX_TRACKER_IS_CLOUD)%'
  token: '%env(YANDEX_TRACKER_TOKEN)%'
  orgId: '%env(YANDEX_TRACKER_ORG_ID)%'
```

Set environment variables before running:
```bash
export YANDEX_TRACKER_IS_CLOUD="true"
export YANDEX_TRACKER_TOKEN="y0_AgAAAABXXXXXXXXXXXXXXXXXXXXXXXXX"
export YANDEX_TRACKER_ORG_ID="123456"
```

Or use `.env` file with Docker:
```bash
# .env
YANDEX_TRACKER_IS_CLOUD=true
YANDEX_TRACKER_TOKEN=y0_AgAAAABXXXXXXXXXXXXXXXXXXXXXXXXX
YANDEX_TRACKER_ORG_ID=123456
```
