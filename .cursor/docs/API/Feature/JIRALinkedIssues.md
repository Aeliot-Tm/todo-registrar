# JIRA Linked Issues

Automatically links newly created JIRA issues to existing ones using configurable link types.

## What It Does

1. Reads `linkedIssues` from inline config (`{EXTRAS: {linkedIssues: ...}}`)
2. After creating the new issue, creates issue links to the specified existing issues
3. Supports single issues, arrays, and different link types per issue

## Link Type Resolution

Link types are matched against JIRA's configured link types by name, inward, or outward description. Matching is case-insensitive and supports prefix matching. Underscore notation (`is_blocked_by`) is supported as legacy syntax.

## Default Link Type

Falls back to `issueLinkType` from the `issue` config section, then to `Relates`.

See [user documentation](../../../../docs/registrar/JIRA/linked_issues.md) for usage formats, link type resolution details, and examples.

## Key Source Paths

- Issue link registrar: `src/Service/Registrar/JIRA/IssueLinkRegistrar.php`
- Link normalizer: `src/Service/Registrar/JIRA/LinkedIssueNormalizer.php`
