# Configuration of GitLab-registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/GitLab/.todo-registrar.php).

Description of keys of general config:
```php
$gitlabConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
        'assignee' => ['username1', 'username2'],   // String or array of strings. Identifiers of GitLab users (username or email),
                                                    // which will be assigned to issue when "assignee-suffix"
                                                    // was not used with tag.
        'labels' => ['label-1', 'label-2'],         // list of labels which will be set to issue
        'tagPrefix' => 'tag-',                      // prefix which will be added to tag when "addTagToLabels=true"
        'milestone' => 123,                         // either ID (integer: 123) or title (string) of milestone (optional)
        'due_date' => '2025-12-31',                 // due date in format YYYY-MM-DD (optional)
    ],
    'service' => [
        'personalAccessToken' => 'string',          // personal access token (for http_token auth method)
        'oauthToken' => 'string',                   // OAuth token (for oauth_token auth method)
        'host' => 'https://gitlab.com',             // GitLab host URL (optional, defaults to https://gitlab.com)
        'project' => 123,                           // either project ID (integer: 123) or project path (string: owner/repo)
                                                    // (projectPath takes priority if both are specified)
    ]
];
```

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

## Inline config

Supported keys of inline config:

| Key       | Description                                                                                                              |
|-----------|--------------------------------------------------------------------------------------------------------------------------|
| assignee  | Identifier(s) of GitLab user(s) (username or email). Can be a string (single user) or array of strings (multiple users). |
| labels    | List of labels which will be assigned to the issue.                                                                      |
| milestone | either ID (integer: 123) or title (string) of milestone (optional)                                                       |
| due_date  | Due date in format YYYY-MM-DD.                                                                                           |

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

