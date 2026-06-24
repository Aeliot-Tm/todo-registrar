# JIRA Registrar

Creates JIRA issues from TODO/FIXME comments in PHP and YAML source files.

## How It Works

1. Builds issue fields via `IssueFieldFactory` and `IssueSupporter`
2. Resolves custom field keys to JIRA field IDs (`customfield_XXXXX`)
3. Creates issue via `IssueService::create()`
4. Registers issue links from inline `linkedIssues` if present
5. Returns issue key (e.g. `PROJ-42`) for key injection

## Issue Fields Mapping

| Source | JIRA field |
|---|---|
| Summary (+ prefix) | `summary` |
| Description (+ context) | `description` |
| Assignee | `assignee` |
| Labels | `labels[]` |
| Components | `components[]` |
| Priority | `priority` |
| Inline `issueType` / config `issueType` | `issuetype` |
| Inline `projectKey` | `project` key |
| `customFields` (config + inline) | custom fields (`customfield_XXXXX`) |
| Inline `linkedIssues` | issue links (after create) |

## Configuration

```yaml
registrar:
  type: JIRA
  options:
    projectKey: PROJ             # fallback if not in issue section
    issueLinkType: Relates       # default link type for linkedIssues list form
    service:
      host: '%env(JIRA_HOST)%'
      personalAccessToken: '%env(JIRA_TOKEN)%'
      tokenBasedAuth: true
      # Alternative: jiraUser + jiraPassword with tokenBasedAuth: false
    issue:
      projectKey: PROJ
      issueType: Task
      assignee: null
      priority: null
      components: []
      labels: []
      customFields: {}          # optional. Values of custom fields which will be applied for all new tickets.
      customFieldsMapping: {}   # optional. Mapping of user-names to system-names. If it is omitted then
                                #           it will be loaded from API and cached for current run (single API call).
      issueLinkType: null
      addTagToLabels: false
      tagPrefix: ''
      allowedLabels: []
      summaryPrefix: ''
      showContext: null
      contextTitle: null
```

**`issueType` vs `type`:** use `issueType`. Legacy `type` is migrated automatically; specifying both raises a validation error.

## Inline Config Keys

| Key | Type |
|---|---|
| `assignee`, `assignees` | string |
| `labels`, `components` | string[] |
| `issueType` | string |
| `priority` | string |
| `projectKey` | string |
| `customFields` | map of field key → value (see Custom Fields) |
| `linkedIssues` | list or map — see [JIRA Linked Issues](JIRALinkedIssues.md) |
| `showContext`, `contextTitle` | string |

Legacy inline key `issue_type` is also accepted in `IssueFieldFactory`.

## Technical Details

| Class | Path |
|---|---|
| Registrar | `src/Service/Registrar/JIRA/JiraRegistrar.php` |
| Factory | `src/Service/Registrar/JIRA/JiraRegistrarFactory.php` |
| Field factory | `src/Service/Registrar/JIRA/IssueFieldFactory.php` |
| Custom field ID resolver | `src/Service/Registrar/JIRA/CustomFieldIdProvider.php` |
| Custom field ID lookup (JIRA API) | `src/Service/Registrar/JIRA/CustomFieldIdFinder.php` |
| Service factory | `src/Service/Registrar/JIRA/ServiceFactory.php` |
| Issue config | `src/Service/Registrar/JIRA/GeneralIssueConfig.php` |

## Custom Fields

Default custom field values can be set in general `issue` config. Per-comment overrides use inline `customFields` in `{EXTRAS: ...}`.

Keys in `customFields` are resolved to a JIRA REST field ID before the issue is created. A key may be:

- field display name (e.g. `My Custom Field`)
- full REST ID (e.g. `customfield_123`)
- numeric field ID (e.g. `123`)
- JQL clause name (e.g. `cf[123]`)

If the key is not found in `customFieldsMapping`, the registrar queries JIRA (`GET /rest/api/2/field`)
and matches against known custom fields. Unknown keys raise an error.

Inline `customFields` override general config values for the same key.

Field values are passed to JIRA as-is. Format depends on the field type (text, select, multi-select, user, date, etc.) — see
[JIRA REST API examples](https://developer.atlassian.com/server/jira/platform/jira-rest-api-examples/#creating-an-issue-using-custom-fields).

Example:

```yaml
issue:
  customFields:
    My Custom Field: "some value"
  customFieldsMapping:
    My Custom Field: customfield_123
```

```php
// TODO: Fix deployment
//       {EXTRAS: {customFields: {"My Custom Field": "some value"}}}
```

`customFieldsMapping` is optional. Use it to pin display names to IDs and skip API lookup, or when the field name alone is ambiguous.

### Custom field resolution flow:

1. `CustomFieldIdProvider` checks `issue.customFieldsMapping` (cached per registrar instance)
2. On cache miss, `CustomFieldIdFinder` loads custom fields via `FieldService::getAllFields(Field::CUSTOM)`
3. `IssueFieldFactory` calls `IssueField::addCustomField($fieldId, $value)` with the resolved ID

---

Library: `lesstif/php-jira-rest-client`.

Related: [JIRA Linked Issues](JIRALinkedIssues.md), [Allowed Labels](AllowedLabels.md), [Context Display](ContextDisplay.md), [Inline Configuration](InlineConfiguration.md).
