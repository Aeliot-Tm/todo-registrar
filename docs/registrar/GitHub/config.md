# Configuration of GitHub-registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/GitHub/.todo-registrar.php).

Description of keys of general config:
```php
$githubConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
        'allowedLabels' => ['label-1', 'label-2'], // optional: list of allowed labels. If set, only labels from this
                                                    //           list will be applied to issues. Labels from inline
                                                    //           config, general config, and tag-based labels (if
                                                    //           addTagToLabels=true) will be filtered to match this list.
        'assignees' => ['an.assignee_1']            // identifiers of GitHub users, which will be assigned to ticket
                                                    // when "assignee-suffix" was not used with tag.
        'labels' => ['a-label'],                    // list of labels which will be set to issue
        'tagPrefix' => 'tag-',                      // prefix which will be added to tag when "addTagToLabels=true"
    ],
    'service' => [
        'personalAccessToken' => 'string',          // personal access-token
        'owner' => 'string'                         // username on GitHub
        'repository' => 'string'                    // the name of repository (part URL to repository)
    ]
];
```

## Allowed Labels

The `allowedLabels` option allows you to restrict which labels can be applied to issues. This is useful when you want to ensure only predefined labels from your repository are used.

### How it works

When `allowedLabels` is set (non-empty array), the registrar filters all collected labels to keep only those that are present in the `allowedLabels` list. The collected labels come from:

1. Labels specified in inline config (via `{EXTRAS: {labels: [...]}}`)
2. Labels from general config (`labels` option)
3. Tag-based label (if `addTagToLabels` is `true`, format: `{tagPrefix}{tag}`)

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
- The general config label `tech-debt` will be applied
- If the tag is `TODO`, the tag-based label `todo-todo` will be filtered out (not in `allowedLabels`)

### When to use

- **Repository policy enforcement**: Ensure only approved labels are used
- **Prevent typos**: Avoid creating issues with misspelled labels
- **Label management**: Control which labels can be created automatically

## Inline config

Supported keys of inline config:

| Key       | Description                                                               |
|-----------|---------------------------------------------------------------------------|
| assignees | List of identifiers of GitHub users, which will be assigned to the issue. |
| labels    | List of labels which will be assigned to the issue.                       |
