# Configuration of JIRA-registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/JIRA/.todo-registrar.php).

Description of keys of general config:
```php
$jiraConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
        'allowedLabels' => ['label-1', 'label-2'], // optional: list of allowed labels. If set, only labels from this
                                                    //           list will be applied to issues. Labels from inline
                                                    //           config, general config, and tag-based labels (if
                                                    //           addTagToLabels=true) will be filtered to match this list.
        'assignee' => 'string'                      // identifier of JIRA-user, which will be assigned to ticket
                                                    // when "assignee-suffix" was not used with tag.
        'components' => ['a-component'],            // list of components which will be set to issue
        'labels' => ['a-label'],                    // list of labels which will be set to issue
        'priority' => 'string',                     // priority of issue
        'tagPrefix' => 'tag-',                      // prefix which will be added to tag when "addTagToLabels=true"
        'type' => 'Bug',                            // type of issue
    ],
    'projectKey' => 'string',                       // key-name of project
    'service' => [
        'host' => 'string',                         // host of JIRA-server
        'personalAccessToken' => 'string',          // personal access-token
        'tokenBasedAuth' => true,

        // JIRA username and password can be used as alternative for authentication on JIRA-server.
        // So, previous option "tokenBasedAuth" must be set to "false".
        //
        // 'jiraUser' => 'string',
        // 'jiraPassword' => 'string',
    ]
];
```

## Allowed Labels

The `allowedLabels` option allows you to restrict which labels can be applied to issues. This is useful when you want to ensure only predefined labels from your JIRA project are used.

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

- **Project policy enforcement**: Ensure only approved labels are used
- **Prevent typos**: Avoid creating issues with misspelled labels
- **Label management**: Control which labels can be created automatically

## Inline config

Supported keys of inline config:

| Key        | Description                                                                                                            |
|------------|------------------------------------------------------------------------------------------------------------------------|
| assignees  | One identifier of JIRA-users as string, which will be assigned to the issue. This one will be used when it is defined. |
| components | List of components which will be set to issue.                                                                         |
| issue_type | Type of issue.                                                                                                         |
| labels     | List of labels which will be assigned to the issue.                                                                    |
| priority   | Priority of issue as string.                                                                                           |
