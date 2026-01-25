# Configuration of Yandex Tracker Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/YandexTracker/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/YandexTracker/.todo-registrar.php)) in the root directory.

### YAML configuration

```yaml
#...
registrar:
  type: YandexTracker
  issue:
    queue: MYQUEUE                                  # required: key of Yandex Tracker queue (required)
    type: task                                      # required: type of issue (task, bug, story, epic, etc.)
    priority: normal                                # priority of issue (blocker, critical, normal, minor, trivial)
    assignee: developer.login                       # optional: login of Yandex Tracker user, which will be assigned to issue
                                                    #           when "assignee-suffix" was not used with tag
    labels:                                         # optional: list of tags which will be set to issue
      - tech-debt
      - from-code
    addTagToLabels: true                            # optional: add detected tag into list of issue tags or not
    tagPrefix: 'tag-'                               # optional: prefix which will be added to tag when "addTagToLabels=true"
    allowedLabels: [tech-debt, another-label]       # optional: list of allowed tags. If set, only tags from this
                                                    #           list will be applied to issues. Tags from inline
                                                    #           config, general config, and tag-based tags (if addTagToLabels=true)
                                                    #           will be filtered to match this list.
    summaryPrefix: '[TODO] '                        # optional: prefix which will be added to issue summary
    showContext: 'numbered'                         # optional: include code context in issue description
                                                    #           values: null (default), 'arrow_chained', 'asterisk', 'code_block',
                                                    #                   'number_sign', 'numbered'
    contextTitle: null                              # optional: title of context path
  service:
    orgId: '%env(YANDEX_TRACKER_ORG_ID)%'           # required: Organization ID (required)
    token: '%env(YANDEX_TRACKER_TOKEN)%'            # required: OAuth token for Yandex Tracker API (required)
    isCloud: true                                   # optional: Is Cloud Organization (is not passes then default: true)
                                                    #           If true, X-Cloud-Org-ID header is passed instead of X-Org-ID
```

### PHP configuration

Description of keys of general config:
```php
$config->setRegistrar('YandexTracker', [
    'issue' => [
        // ...
        // See description of keys in YAML config above
    ],
    'service' => [
        'orgId' => $_ENV['YANDEX_TRACKER_ORG_ID'],
        'token' => $_ENV['YANDEX_TRACKER_TOKEN'],
        'isCloud' => true,
    ]
]);
```

### Option 'queue'

It is expected that `queue` is in `issue` array, but script tries to get it from root too.
And it can be overridden by inline config.

### Option 'isCloud'

If true, X-Cloud-Org-ID header is passed instead of X-Org-ID.

You may pass different comfortable literals to it.

| Resolved value | Literals |
|---|---|
| true | true (bool or string), 1 (int or string), y (string), yes (string) |
| false | false (bool or string), 0 (int or string), n (string), no (string), not (string) |

### Option allowedLabels

See [allowed labels documentation](../../allowed_labels.md)

### Option showContext

See [show context documentation](../../context_display.md)

## Inline config

Supported keys of [inline config](../../inline_config.md):

| Key | Description |
|---|---|
| assignee | Login of Yandex Tracker user as string, which will be assigned to the issue. This one will be used when it is defined. |
| issue_type | Type of issue (task, bug, story, epic, etc.). |
| labels | List of tags which will be assigned to the issue. |
| priority | Priority of issue as string (blocker, critical, normal, minor, trivial). |

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

![UI for Organization ID](ui_org_id.png)

## Environment Variables

For security, use environment variables for sensitive data.

First of all define in the config which environment variables have to be used.
Expressions `%env(...)%` will be detected in YAML file and replaced by values of related environment variables.
See documentation of used package [aeliot/env-resolver](https://github.com/Aeliot-Tm/env-resolver) for more details.

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
