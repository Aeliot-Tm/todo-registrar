# Configuration of GitLab Registrar

## General config

Put config php-file `.todo-registrar.php` in the root directory.
See [example](../../../examples/GitLab/.todo-registrar.php).

Description of keys of general config:
```php
$gitlabConfig = [
    'issue' => [
        'addTagToLabels' => true,                   // add detected tag into list of issue labels or not
        'allowedLabels' => ['label-1', 'label-2'], // optional: list of allowed labels. If set, only labels from this
                                                    //           list will be applied to issues. Labels from inline
                                                    //           config, general config, and tag-based labels (if
                                                    //           addTagToLabels=true) will be filtered to match this list.
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

## Allowed Labels

The `allowedLabels` option allows you to restrict which labels can be applied to issues. This is useful when you want to ensure only predefined labels from your project are used.

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

