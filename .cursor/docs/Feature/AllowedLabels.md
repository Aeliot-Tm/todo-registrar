# Allowed Labels

Restricts which labels can be applied to created issues by filtering against a predefined list.

## What It Does

1. Collects labels from all sources: general config, tag-based labels, inline config
2. Filters the collected labels to keep only those present in the `allowedLabels` list
3. Applies only the filtered labels to the created issue

## Supported Registrars

- GitHub
- GitLab
- JIRA
- Yandex Tracker

Redmine does not support labels, so this option is ignored for Redmine.

## Configuration

Configured via `allowedLabels` option in the registrar's `issue` section.

See [user documentation](../../../docs/allowed_labels.md) for filtering rules and usage examples.

## Key Source Paths

- Label filtering: `src/Service/Registrar/IssueSupporter.php`
