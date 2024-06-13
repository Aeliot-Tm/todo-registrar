# Configuration of JIRA-registrar

Description of keys:
```php
$jiraConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
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