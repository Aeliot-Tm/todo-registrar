# Allowed Labels

Restricts which labels (or Yandex Tracker tags) are applied to newly created issues.

## What It Does

1. Collects labels from inline config, general `issue.labels`, and optionally from the tag name (`addTagToLabels`)
2. If `allowedLabels` is non-empty, keeps only labels present in that list (`array_intersect`)
3. Passes the filtered list to the registrar

When `allowedLabels` is empty (default), no filtering is applied.

## Supported Registrars

| Registrar | Field |
|---|---|
| GitHub | `labels[]` |
| GitLab | `labels` |
| JIRA | `labels[]` |
| Yandex Tracker | `tags[]` (labels are mapped to tags) |
| Redmine | ignored (Redmine has no labels) |

## Configuration

```yaml
registrar:
  options:
    issue:
      labels: [bug, feature, tech-debt]
      allowedLabels: [bug, feature]   # tech-debt will be dropped
      addTagToLabels: true            # tag label also filtered
      tagPrefix: 'tag-'
```

Inline `labels` in `{EXTRAS: ...}` are filtered the same way.

## Technical Details

Label merging and filtering: `src/Service/Registrar/IssueSupporter.php` (`getLabels()`).

Shared issue options validated in `src/Service/Registrar/AbstractGeneralIssueConfig.php`.
