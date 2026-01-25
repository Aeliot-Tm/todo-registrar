# Configuration of JIRA Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/JIRA/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/JIRA/.todo-registrar.php)) in the root directory.

### YAML configuration

```yaml
#...
registrar:
  type: JIRA
  issue:
      projectKey: 'string'                    # required: key-name of project
      type: 'Bug'                             # required: type of issue
      priority: 'string'                      # required: priority of issue
      assignee: 'string'                      # optional: identifier of JIRA-user, which will be assigned to ticket
                                              #           when "assignee-suffix" was not used with tag.
      labels: ['a-label']                     # optional: list of labels which will be set to issue
      addTagToLabels: true                    # optional: add detected tag into list of issue labels or not
      tagPrefix: 'tag-'                       # optional: prefix which will be added to tag when "addTagToLabels=true"
      allowedLabels: ['label-1', 'label-2']   # optional: list of allowed labels. If set, only labels from this
                                              #           list will be applied to issues. Labels from inline
                                              #           config, general config, and tag-based labels (if
                                              #           addTagToLabels=true) will be filtered to match this list.
      components: ['a-component']             # optional: list of components which will be set to issue
      summaryPrefix: '[TODO] '                # optional: prefix which will be added to issue subject
      showContext: true                       # optional: include code context in issue description
                                              #           values: false (default), true, 'arrow_chained', 'code_block',
                                              #                   'number_sign', 'numbered'
  service:
      host: '%env(JIRA_HOST)%'                                  # required: host of JIRA-server
      personalAccessToken: '%env(JIRA_PERSONAL_ACCESS_TOKEN)%'  # optional: personal access-token
      tokenBasedAuth: true                                      # optional: (default: false)

      # JIRA username and password can be used as alternative for authentication on JIRA-server.
      # So, previous option "tokenBasedAuth" must be set to "false".
      #
      # jiraUser: 'string'
      # jiraPassword: 'string'
```

### PHP configuration

Description of keys of general config:
```php
$config->setRegistrar('JIRA', [
    'issue' => [
        // ...
        // See description of keys in YAML config above
    ],
    'service' => [
        'host' => $_ENV['JIRA_HOST'],
        'personalAccessToken' => $_ENV['JIRA_PERSONAL_ACCESS_TOKEN'],
        'tokenBasedAuth' => true,
    ]
]);
```

### Option 'projectKey'

It is expected that `projectKey` is in `issue` array, but script tries to get it from root too.
And it can be overridden by inline config.

### Option allowedLabels

See [allowed labels documentation](../../allowed_labels.md)

### Option showContext

See [show context documentation](../../context_display.md)

## Inline config

Supported keys of inline config:

| Key | Description |
|---|---|
| assignees | One identifier of JIRA-users as string, which will be assigned to the issue. This one will be used when it is defined. |
| components | List of components which will be set to issue. |
| issue_type | Type of issue. |
| labels | List of labels which will be assigned to the issue. |
| priority | Priority of issue as string. |
| projectKey | Allow override project key to create issue in another related project. |
