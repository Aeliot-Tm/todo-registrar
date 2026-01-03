# Configuration of Redmine Registrar

## General config

Put either yaml-config-file `.todo-registrar.yaml` ([example](../../../examples/Redmine/.todo-registrar.yaml))
or php-config-file `.todo-registrar.php` ([example](../../../examples/Redmine/.todo-registrar.php)) in the root directory.

Description of keys of general config:
```php
$config->setRegistrar('Redmine', [
    'issue' => [
        'tracker' => 'Bugs',                     // required: tracker name or ID
        'priority' => 'Low',                     // optional: priority name or ID
        'assignee' => null,                      // optional: username, login, email, or user ID
        'category' => null,                      // optional: category name or ID
        'fixed_version' => null,                 // optional: version name or ID
        'start_date' => null,                    // optional: start date in format YYYY-MM-DD
        'due_date' => null,                      // optional: due date in format YYYY-MM-DD
        'estimated_hours' => null,               // optional: estimated hours as float
        'summaryPrefix' => '[TODO] ',            // optional: prefix which will be added to issue subject
    ],
    'project' => 'testing-project',              // required: project identifier or ID
    'service' => [
        'url' => 'https://redmine.example.com',  // required: Redmine URL
        'apikeyOrUsername' => 'string',          // required: API key (recommended) or username
        'password' => null,                      // optional: password for Basic Auth
                                                 //           If password is provided, Basic Auth will be used (username:password)
                                                 //           Otherwise, apikeyOrUsername will be treated as API key
    ]
]);
```

## Labels

Redmine doesn't support labels.

## Authentication methods

Redmine registrar supports two authentication methods:

### API Key (Recommended)

This is the default and recommended method. Use an API key created in Redmine settings.

```php
'service' => [
    'url' => 'https://redmine.example.com',
    'apikeyOrUsername' => 'your-api-key',
]
```

Generate API key in Redmine: **My account** → **API access key**

### Username/Password (Basic Auth)

For Basic HTTP authentication:

```php
'service' => [
    'url' => 'https://redmine.example.com',
    'apikeyOrUsername' => 'username',
    'password' => 'password',
]
```

If `password` is provided, `apikeyOrUsername` is treated as username and Basic Auth is used. Otherwise, it's treated as API key.

## Project identification

You can identify the project either by identifier or by ID:

### Using Project Identifier

```php
'project' => 'testing-project',
```

### Using Project ID

```php
'project' => 1,
```

The registrar automatically resolves project identifiers to IDs via the Projects API. Results are cached for performance.

## Entity resolution

Tracker, priority, category, and version can be specified by ID (integer) or name (string). Names are automatically resolved to IDs via Redmine API. Results are cached for performance.

- **Tracker**: Resolved via `/trackers.xml` endpoint
- **Priority**: Resolved via `/enumerations/issue_priorities.xml` endpoint
- **Category**: Resolved via `/projects/{id}/issue_categories.xml` endpoint (requires project ID)
- **Version**: Resolved via `/projects/{id}/versions.xml` endpoint (requires project ID)

### Examples

1. Using names (more readable):
   ```php
   'tracker' => 'Bugs',
   'priority' => 'High',
   'category' => 'Categ B',
   'fixed_version' => 'v0.0.2',
   ```

2. Using IDs (more efficient):
   ```php
   'tracker' => 1,
   'priority' => 5,
   'category' => 2,
   'fixed_version' => 2,
   ```

## Self-hosted Redmine

For self-hosted Redmine instances, specify the host URL:

```php
'service' => [
    'url' => 'https://redmine.yourdomain.com',
    'apikeyOrUsername' => 'your-api-key',
]
```

### Local Redmine in Docker

If you're running Redmine locally in Docker and the registrar runs in a Docker container, use `host.docker.internal` instead of `localhost`:

```php
'service' => [
    'url' => 'http://host.docker.internal:3000',
    'apikeyOrUsername' => 'your-api-key',
]
```

## Inline config

Supported keys of inline config:

| Key            | Type              | Description                                            |
|----------------|-------------------|--------------------------------------------------------|
| assignee       | `string` or `int` | Username/login/email or user ID to assign to the issue |
| tracker        | `int` or `string` | Tracker ID or name (Bug, Feature, Support, etc.)       |
| priority       | `int` or `string` | Priority ID or name (High, Normal, Low, etc.)          |
| category       | `int` or `string` | Category ID or name                                    |
| fixed_version  | `int` or `string` | Version ID or name                                     |
| start_date     | `string`          | Start date in format YYYY-MM-DD                        |
| due_date       | `string`          | Due date in format YYYY-MM-DD                          |
| estimated_hours| `float`           | Estimated hours                                        |

### Examples

1. Assign user and set priority:
   ```
   {EXTRAS: {assignee: john, priority: High}}
   ```

2. Set tracker, category, and due date:
   ```
   {EXTRAS: {tracker: Bug, category: Categ B, due_date: 2025-12-31}}
   ```

3. Set version and estimated hours:
   ```
   {EXTRAS: {fixed_version: v0.0.2, estimated_hours: 8.5}}
   ```

4. Use tag assignee syntax:
   ```
   TODO@john: Fix this bug
   ```

## Priority of values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignee: user1}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignee` in config file

### Assignee examples

1. Set default assignee in global config:
   ```php
   'issue' => [
       'assignee' => 'john',  // username, login, or email
   ]
   ```

2. Override with inline config:
   ```
   {EXTRAS: {assignee: jane}}
   ```

3. Use tag assignee syntax:
   ```
   TODO@john: Fix this bug
   ```

## Notes

- **User Resolution**: Usernames, logins, and emails are automatically resolved to Redmine user IDs via the Users API. Results are cached to optimize performance. User IDs can also be specified directly.
- **Entity Resolution**: All entity names (tracker, priority, category, version) are resolved to IDs via Redmine API. Results are cached for performance.
- **Project Resolution**: Project identifiers are resolved to project IDs via the Projects API with pagination support.
- **Required Fields**: `project` and `tracker` are required. All other fields are optional.
- **Date Format**: All date fields must be in format `YYYY-MM-DD`.
- **API Permissions**: Make sure your API key has permissions to create issues in the specified project. Check project settings and user role permissions in Redmine.
