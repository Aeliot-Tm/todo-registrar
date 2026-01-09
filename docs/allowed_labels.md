# Option Allowed Labels

The `allowedLabels` option allows you to restrict which tags can be applied to issues.
This is useful when you want to ensure only predefined tags from your Yandex Tracker queue are used.

### How it works

When `allowedLabels` is set (non-empty array), the registrar filters all collected tags
to keep only those that are present in the `allowedLabels` list. The collected tags come from:

1. Labels from [general config](config/general_config) (`labels` option)
2. Tag-based label (if `addTagToLabels` is `true`, format: `{tagPrefix}{tag}`)
3. Labels specified in [inline config](inline_config.md) (via `{EXTRAS: {labels: [...]}}`)

### Example

```php
'issue' => [
    'addTagToLabels' => true,
    'allowedLabels' => ['bug', 'feature', 'tech-debt'],
    'labels' => ['tech-debt'],
    'tagPrefix' => 'tag-',
]
```

With this configuration:
- If a TODO comment has `{EXTRAS: {labels: [bug, urgent]}}`, only `bug` will be applied (because `urgent` is not in `allowedLabels`)
- The general config tag `tech-debt` will be applied
- If the tag is `TODO`, the tag-based tag `tag-todo` will be filtered out (not in `allowedLabels`)

### When to use

- **Queue policy enforcement**: Ensure only approved tags are used
- **Prevent typos**: Avoid creating issues with misspelled tags
- **Tag management**: Control which tags can be created automatically
