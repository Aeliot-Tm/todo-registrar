# Linked issues

> What is linked issues in JIRA?: [link work items](https://support.atlassian.com/jira-software-cloud/docs/link-issues/).

When a new JIRA issue is created from a TODO comment, you can automatically link it
to one or more existing issues. This is useful when your TODO is related to, blocked by,
or otherwise connected to tickets that already exist in JIRA.

Linked issues are configured through [inline config](../../inline_config.md) using the `linkedIssues` key.

## Quick start

Add `linkedIssues` to the `EXTRAS` block of your TODO comment:

```php
/**
 * TODO: fix input validation
 *       {EXTRAS: {linkedIssues: PROJ-100}}
 */
```

The created issue will be linked to `PROJ-100` with the default link type.

## Default link type

If you don't specify a link type, the value from the `issueLinkType` option
in the `issue` section of the [general config](config.md) is used.
If that option is not set either, the script falls back to `issueLinkType`
at the root of registrar options (for backward compatibility). If neither is defined, the default is `Relates`.

```yaml
registrar:
  options:
    issue:
      issueLinkType: 'Blocks'   # change the default link type
```

## Usage formats

### Single issue

```
{EXTRAS: {linkedIssues: PROJ-100}}
```

### Multiple issues with the same link type

```
{EXTRAS: {linkedIssues: [PROJ-100, PROJ-200]}}
```

Both issues will be linked using the default link type.

### Multiple issues with different link types

Use quoted keys to specify link type names that contain spaces:

```
{EXTRAS: {linkedIssues: {"is child of": PROJ-100, "is blocked by": [PROJ-200, PROJ-300]}}}
```

Here `PROJ-100` is linked as a parent, while `PROJ-200` and `PROJ-300` are linked as blockers.

## How link types are resolved

The value you write as a link type is matched against the link types configured
in your JIRA instance. Each JIRA link type has three names:

| Field    | Example (for "Blocks") |
|----------|------------------------|
| name     | Blocks                 |
| inward   | is blocked by          |
| outward  | blocks                 |

You can use any of these three values as the key in `linkedIssues`.
Matching is **case-insensitive** and works by prefix, so abbreviations are accepted
as long as they are unambiguous.

See description of JIRA:
1. [Jira issue linking model](https://developer.atlassian.com/cloud/jira/platform/issue-linking-model/)
2. [Configure work item linking](https://support.atlassian.com/jira-cloud-administration/docs/configure-issue-linking/)
3. [REST API v3 (Cloud)](https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issue-link-types/#api-rest-api-3-issuelinktype-get)

### Quoted keys (recommended)

If the link type name contains spaces or special characters, wrap it in double quotes.
This is the recommended approach as it allows using JIRA link type names exactly as they appear
in your JIRA instance:

```
{EXTRAS: {linkedIssues: {"is blocked by": [PROJ-200, PROJ-300]}}}
```

For single-word link types, quotes are not required:

```
{EXTRAS: {linkedIssues: {Blocks: PROJ-100, Relates: [PROJ-200]}}}
```

See [inline config](../../inline_config.md#multi-word-keys-and-values) for details on quoting syntax.

### Underscore notation (legacy)

> This approach is supported for backward compatibility.
> Prefer using [quoted keys](#quoted-keys-recommended) instead.

You can replace spaces with underscores in link type names.
For example, `is_blocked_by` is equivalent to `is blocked by`.

The combination `_to_` is also recognized as ` -> `, which helps with Gantt-style
link types like `Start -> End [Gantt]` — you can write `start_to_end_gantt_`.

### Common aliases

Below are examples of aliases for the most frequently used link types.
The actual set of link types depends on your JIRA instance.

| Link type         | Accepted aliases |
|-------------------|------------------|
| Blocks            | `Blocks`, `blocks`, `"is blocked by"`, `is_blocked_by` |
| Duplicate         | `Duplicate`, `duplicates`, `"is duplicated by"`, `is_duplicated_by` |
| Relates           | `Relates`, `"relates to"`, `relates_to` |
| Cloners           | `Cloners`, `clones`, `"is cloned by"`, `is_cloned_by` |
| Parent-Child      | `Parent-Child`, `"is parent of"`, `is_parent_of`, `"is child of"`, `is_child_of` |
| Problem/Incident  | `Problem/Incident`, `problem_incident`, `causes`, `"is caused by"`, `is_caused_by` |

## Full example

```php
/**
 * TODO@john.doe: refactor payment module
 *                The current implementation has performance issues
 *                and duplicates logic from the billing service.
 *                {EXTRAS: {
 *                   linkedIssues: {
 *                      "is blocked by": [PAY-42, PAY-58],
 *                      Relates: [BILL-10]
 *                   },
 *                   labels: [refactoring, performance]
 *                }}
 */
```

This will create a new issue assigned to `john.doe`, link it as blocked by
`PAY-42` and `PAY-58`, link it as related to `BILL-10`, and add labels
`refactoring` and `performance`.
