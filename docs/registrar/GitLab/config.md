# Configuration of GitLab Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/GitLab/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/GitLab/.todo-registrar.php)) in the root directory.

### YAML configuration

```yaml
#...
registrar:
  type: GitLab
  issue:
      project: 123                            # required: either project ID (integer: 123) or project path (string: owner/repo)
      assignee: ['username1', 'username2']    # optional: String or array of strings. Identifiers of GitLab users (username or email),
                                              #           which will be assigned to issue when "assignee-suffix"
                                              #           was not used with tag.
      labels: ['label-1', 'label-2']          # optional: list of labels which will be set to issue
      addTagToLabels: true                    # optional: add detected tag into list of issue labels or not
      tagPrefix: 'tag-'                       # optional: prefix which will be added to tag when "addTagToLabels=true"
      allowedLabels: ['label-1', 'label-2']   # optional: list of allowed labels. If set, only labels from this
                                              #           list will be applied to issues. Labels from inline
                                              #           config, general config, and tag-based labels (if addTagToLabels=true)
                                              #           will be filtered to match this list.
      due_date: '2025-12-31'                  # optional: due date in format YYYY-MM-DD (optional)
      milestone: 123                          # optional: either ID (integer: 123) or title (string) of milestone (optional)
      summaryPrefix: '[TODO] '                # optional: prefix which will be added to issue subject
                                              #           supports dynamic placeholders: {tag}, {tag_caps}, {assignee}
      showContext: 'numbered'                 # optional: include code context in issue description
                                              #           values: null (default), 'arrow_chained', 'asterisk', 'code_block',
                                              #                   'number_sign', 'numbered'
      contextTitle: null                      # optional: title of context path
  service:
      host: 'https://gitlab.com',                                   # optional: GitLab host URL (optional, defaults to https://gitlab.com)
      personalAccessToken: '%env(GITLAB_PERSONAL_ACCESS_TOKEN)%',   # optional: personal access token (for http_token auth method)
      oauthToken: '%env(GITLAB_PERSONAL_OAUTH_TOKEN)%',             # optional: OAuth token (for oauth_token auth method)
```

### PHP configuration

Description of keys of general config:
```php
$config->setRegistrar('GitLab', [
    'issue' => [
        // ...
        // See description of keys in YAML config above
    ],
    'service' => [
        'host' => 'https://gitlab.com',
        'personalAccessToken' => $_ENV['GITLAB_PERSONAL_ACCESS_TOKEN'],
        'oauthToken' => $_ENV['GITLAB_PERSONAL_OAUTH_TOKEN'],
    ]
]);
```

> **Note:** either `personalAccessToken` or `oauthToken` is required.

### Option 'project'

It is expected that `project` is in `issue` array, but script tries to get it from `service` too.
And it can be overridden by inline config.

## Authentication methods

GitLab registrar supports two authentication methods:

### HTTP Token (Personal Access Token)

This is the default and recommended method. Use a Personal Access Token created in GitLab settings.

```php
'service' => [
    'personalAccessToken' => 'your_token_here',
    // ...
]
```

### OAuth Token

For OAuth 2.0 authentication:

```php
'service' => [
    'oauthToken' => 'your_oauth_token_here',
    // ...
]
```

## Project identification

You can identify the project either by ID or by path:

### Using Project ID

```php
'service' => [
    'project' => 123,
    // ...
]
```

### Using Project Path

```php
'service' => [
    'project' => 'owner/repo',
    // ...
]
```

## Self-hosted GitLab

For self-hosted GitLab instances, specify the host URL:

```php
'service' => [
    'host' => 'https://gitlab.yourdomain.com',
    'personalAccessToken' => 'your_token',
    // ...
]
```

### Option allowedLabels

See [allowed labels documentation](../../allowed_labels.md)

### Option showContext

See [show context documentation](../../context_display.md)

### Option summaryPrefix

See [dynamic summary prefix documentation](../../dynamic_summary_prefix.md)

## Inline config

Supported keys of inline config:

| Key | Description |
|---|---|
| assignee | Identifier(s) of GitLab user(s) (username or email). Can be a string (single user) or array of strings (multiple users) |
| labels | List of labels which will be assigned to the issue |
| milestone | either ID (integer: 123) or title (string) of milestone (optional) |
| due_date | Due date in format YYYY-MM-DD |

### Examples

1. Assign single user:
   ```
   {EXTRAS: {assignee: username1}}
   ```

2. Assign multiple users:
   ```
   {EXTRAS: {assignee: [username1, username2]}}
   ```

3. Set labels and milestone:
   ```
   {EXTRAS: {labels: [bug, urgent], milestone: 5}}
   ```

4. Find milestone by title:
   ```
   {EXTRAS: {milestone: Sprint_1}}
   ```

5. Set due date:
   ```
   {EXTRAS: {due_date: 2025-12-31}}
   ```

## Notes

- **Labels**: Labels are created at project level if they don't exist. The registrar automatically creates missing labels before creating the issue.
- **Milestones**: Milestone IDs are validated before creating the issue. If a milestone doesn't exist, an error will be thrown.
- **Assignees**: Usernames and emails are automatically resolved to user IDs via GitLab Users API. Results are cached to optimize performance.
- **Project Path vs ID**: Using <project path> is more readable but may require an additional API call. Using <project id> is more efficient.
