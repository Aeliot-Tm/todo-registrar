# Configuration of GitHub-registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/GitHub/.todo-registrar.php).

Description of keys of general config:
```php
$githubConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
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

## Inline config

Supported keys of inline config:

| Key       | Description                                                               |
|-----------|---------------------------------------------------------------------------|
| assignees | List of identifiers of GitHub users, which will be assigned to the issue. |
| labels    | List of labels which will be assigned to the issue.                       |
