# Configuration of GitHub-registrar

See [example](../../../examples/config.github.php)

Description of keys:
```php
$githubConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
        'assignees' => ['an.assignee_1']            // identifiers of Github-users, which will be assigned to ticket
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
