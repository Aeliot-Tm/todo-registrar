# JIRA Linked Issues

Creates issue links from a newly registered JIRA issue to existing issues specified in inline config.

## What It Does

1. After `IssueService::create()`, `JiraRegistrar` calls `IssueLinkRegistrar::registerLinks()`
2. Reads `linkedIssues` from inline config only (not from general config)
3. Normalizes format and creates inward → outward links via JIRA REST API

## Inline Config Formats

**List** — all keys linked with default type:

```php
// TODO: Depends on auth refactor
//       {EXTRAS: {linkedIssues: [AUTH-1, AUTH-2]}}
```

**Map** — link type alias per group:

```php
// TODO: Blocked work
//       {EXTRAS: {linkedIssues: {blocks: [TASK-10], relates: [TASK-11]}}}
```

## Default Link Type

Resolved in order:

1. `registrar.options.issue.issueLinkType`
2. `registrar.options.issueLinkType` (top-level in options)
3. `'Relates'`

## Link Type Resolution

`IssueLinkTypeProvider` loads link types from JIRA and matches alias against:

- Exact `name`, `inward`, or `outward`
- Case-insensitive prefix match
- Underscore notation (`is_blocked_by`) as legacy alias syntax

Unknown alias → `InvalidInlineConfigFormatException`.

## Technical Details

| Class | Path |
|---|---|
| Link registration | `src/Service/Registrar/JIRA/IssueLinkRegistrar.php` |
| Normalizer | `src/Service/Registrar/JIRA/LinkedIssueNormalizer.php` |
| Type provider | `src/Service/Registrar/JIRA/IssueLinkTypeProvider.php` |
| Orchestration | `src/Service/Registrar/JIRA/JiraRegistrar.php` |

See also: [JIRA Registrar](JIRARegistrar.md), [Inline Configuration](InlineConfiguration.md).
