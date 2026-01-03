# Configuration of GitHub Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/GitHub/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/GitHub/.todo-registrar.php)) in the root directory.

Description of keys of general config:
```php
$config->setRegistrar('GitHub', [
    'issue' => [
        'assignees' => ['an.assignee_1']            // optional: identifiers of GitHub users, which will be assigned to ticket
                                                    //           when "assignee-suffix" was not used with tag.
        'labels' => ['a-label'],                    // optional: list of labels which will be set to issue
        'addTagToLabels' => true,                   // optional: add detected tag into list of issue labels or not
        'tagPrefix' => 'tag-',                      // optional: prefix which will be added to tag when "addTagToLabels=true"
        'allowedLabels' => ['label-1', 'label-2'],  // optional: list of allowed labels. If set, only labels from this
                                                    //           list will be applied to issues. Labels from inline
                                                    //           config, general config, and tag-based labels (if
                                                    //           addTagToLabels=true) will be filtered to match this list.
    ],
    'service' => [
        'personalAccessToken' => 'string',          // required: personal access-token
        'owner' => 'string'                         // required: username on GitHub
        'repository' => 'string'                    // required: the name of repository (part URL to repository)
    ]
]);
```

## Allowed Labels

See [allowed labels documentation](../../allowed_labels.md)

## Inline config

Supported keys of inline config:

| Key       | Description                                                               |
|-----------|---------------------------------------------------------------------------|
| assignees | List of identifiers of GitHub users, which will be assigned to the issue. |
| labels    | List of labels which will be assigned to the issue.                       |
