# Configuration of GitHub Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/GitHub/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/GitHub/.todo-registrar.php)) in the root
directory.

### YAML configuration

```yaml
#...
registrar:
  type: GitHub
  issue:
      repository: 'string'                    # required: the name of repository (part URL to repository)
      owner: 'string'                         # optional: username on GitHub
      assignees: ['an.assignee_1']            # optional: identifiers of GitHub users, which will be assigned to ticket
                                              #           when "assignee-suffix" was not used with tag.
      labels: ['a-label']                     # optional: list of labels which will be set to issue
      addTagToLabels: true                    # optional: add detected tag into list of issue labels or not
      tagPrefix: 'tag-'                       # optional: prefix which will be added to tag when "addTagToLabels=true"
      allowedLabels: ['label-1', 'label-2']   # optional: list of allowed labels. If set, only labels from this
                                              #           list will be applied to issues. Labels from inline
                                              #           config, general config, and tag-based labels (if
                                              #           addTagToLabels=true) will be filtered to match this list.
      summaryPrefix: '[TODO] '                # optional: prefix which will be added to issue subject
      showContext: 'numbered'                 # optional: include code context in issue description
                                              #           values: null (default), 'arrow_chained', 'asterisk', 'asterisk', 'code_block',
                                              #                   'number_sign', 'numbered'
  service:
      personalAccessToken: '%env(GITHUB_PERSONAL_ACCESS_TOKEN)%',   # required: personal access-token
```

### PHP configuration

Description of keys of general config:

```php
$config->setRegistrar('GitHub', [
    'issue' => [
        // ...
        // See description of keys in YAML config above
    ],
    'service' => [
        'personalAccessToken' => $_ENV['GITHUB_PERSONAL_ACCESS_TOKEN'],
    ]
]);
```

### Options 'owner' & 'repository'

It is expected that `owner` & `repository` is in `issue` array, but script tries to get it from `service` root too.
Option `owner` can be omitted when `repository` match pattern `{owner}/{repository}`. Otherwise, it is required!

They can be overridden by inline config. If `repository` in inline config match pattern `{owner}/{repository}`
then `owner` will be taken from `repository`.

### Option allowedLabels

See [allowed labels documentation](../../allowed_labels.md)

### Option showContext

See [show context documentation](../../context_display.md)

## Inline config

Supported keys of inline config:

| Key | Description |
|---|---|
| assignees | List of identifiers of GitHub users, which will be assigned to the issue |
| labels | List of labels which will be assigned to the issue |
| owner | username on GitHub |
| repository | the name of repository (part URL to repository) |
