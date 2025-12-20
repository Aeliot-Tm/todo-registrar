# JIRA Registrar

## Overview

JIRA Registrar creates issues in Atlassian JIRA from TODO/FIXME comments found in source code.

## How It Works

1. Parses TODO comment to extract summary, description, assignee, and inline config
2. Creates JIRA issue with configured project key, type, and fields
3. Registers issue links if specified in inline config
4. Returns issue key (e.g., `PROJ-123`) which is injected back into comment

## Issue Fields Mapping

| TODO Comment | JIRA Issue Field |
|--------------|-----------------|
| Summary (first line) | `summary` |
| Description (full text) | `description` |
| Tag assignee (`TODO@username`) | `assignee` |
| Inline config `assignee` | `assignee` |
| Config `issue.assignee` | `assignee` |
| Inline config `labels` | `labels[]` |
| Config `issue.labels` | `labels[]` |
| Tag name (if `addTagToLabels=true`) | `labels[]` |
| Inline config `components` | `components[]` |
| Config `issue.components` | `components[]` |
| Inline config `priority` | `priority` |
| Config `issue.priority` | `priority` |
| Inline config `issue_type` | `issuetype` |
| Config `issue.type` | `issuetype` |
| Inline config `linkedIssues` | Issue links |

## Configuration

### Service Configuration

```yaml
registrar:
  type: JIRA
  projectKey: 'PROJ'                       # JIRA project key
  issueLinkType: 'Relates'                 # Default link type (optional)
  service:
    host: 'https://company.atlassian.net'  # JIRA host
    personalAccessToken: 'token'           # Personal Access Token
    tokenBasedAuth: true                   # Use token authentication
    
    # Alternative: username/password auth (tokenBasedAuth: false)
    # jiraUser: 'username'
    # jiraPassword: 'password'
```

### Issue Configuration

```yaml
registrar:
  issue:
    type: 'Task'                 # Default issue type (Bug, Task, Story, etc.)
    addTagToLabels: true         # Add TODO/FIXME tag as label
    tagPrefix: 'tag-'            # Prefix for tag label (e.g., "tag-todo")
    labels:                      # Default labels for all issues
      - tech-debt
      - from-code
    components:                  # Default components
      - Backend
      - API
    assignee: 'developer1'       # Default assignee
    priority: 'Medium'           # Default priority
    summaryPrefix: '[TODO] '     # Prefix for issue summary
```

## Inline Configuration

Specify per-comment settings using `{EXTRAS: {...}}` syntax:

```php
// TODO: Fix this bug
//       {EXTRAS: {issue_type: Bug, priority: High, components: [Frontend], labels: [urgent]}}
```

### Supported Inline Config Keys

| Key | Type | Description |
|-----|------|-------------|
| `assignee` | `string` | JIRA username to assign |
| `issue_type` | `string` | Issue type (Bug, Task, Story, etc.) |
| `priority` | `string` | Priority name (Highest, High, Medium, Low, Lowest) |
| `labels` | `string[]` | List of labels to add |
| `components` | `string[]` | List of JIRA components |
| `linkedIssues` | `array` | Issue links (see below) |

## Issue Linking

JIRA Registrar supports creating issue links to relate the new issue to existing issues.

### Simple Format (Default Link Type)

```php
// TODO: Implement feature
//       {EXTRAS: {linkedIssues: [PROJ-100, PROJ-101]}}
```

Uses the default link type configured in `issueLinkType` (default: "Relates").

### Explicit Link Type Format

```php
// TODO: Fix this bug
//       {EXTRAS: {linkedIssues: {Blocks: [PROJ-100], "Is Blocked By": [PROJ-101]}}}
```

### Common JIRA Link Types

| Link Type | Inward Description | Outward Description |
|-----------|-------------------|-------------------|
| `Blocks` | is blocked by | blocks |
| `Cloners` | is cloned by | clones |
| `Duplicate` | is duplicated by | duplicates |
| `Relates` | relates to | relates to |

## Priority of Values

When the same field can be set from multiple sources, priority is (highest to lowest):

1. **Inline config** — `{EXTRAS: {assignee: user1}}`
2. **Tag assignee** — `TODO@username`
3. **Global config** — `issue.assignee` in config file

## Example

### Comment in Code

```php
/**
 * TODO@john: Refactor authentication module
 *            Current implementation has security issues
 *            {EXTRAS: {issue_type: Bug, priority: High, components: [Security], linkedIssues: [PROJ-500]}}
 */
function authenticate() {
    // ...
}
```

### Created JIRA Issue

- **Project**: PROJ
- **Type**: Bug
- **Summary**: `Refactor authentication module`
- **Description**: `Current implementation has security issues`
- **Assignee**: john
- **Priority**: High
- **Components**: Security
- **Labels**: `todo` (if `addTagToLabels=true`)
- **Links**: Relates to PROJ-500

### Result in Code

```php
/**
 * TODO: PROJ-42 Refactor authentication module
 *       Current implementation has security issues
 *       {EXTRAS: {issue_type: Bug, priority: High, components: [Security], linkedIssues: [PROJ-500]}}
 */
```

## Authentication Methods

### Personal Access Token (Recommended)

```yaml
service:
  host: 'https://company.atlassian.net'
  personalAccessToken: 'your-token'
  tokenBasedAuth: true
```

### Username/Password (Legacy)

```yaml
service:
  host: 'https://jira.company.com'
  jiraUser: 'username'
  jiraPassword: 'password'
  tokenBasedAuth: false
```

## Technical Details

### Key Classes

| Class | Responsibility |
|-------|----------------|
| `JiraRegistrar` | Main registrar, orchestrates issue creation |
| `JiraRegistrarFactory` | Creates registrar from config |
| `IssueFieldFactory` | Builds IssueField from Todo |
| `IssueConfig` | Holds parsed issue configuration |
| `ServiceFactory` | Creates JIRA API service clients |
| `IssueLinkRegistrar` | Creates issue links after issue creation |
| `LinkedIssueNormalizer` | Normalizes linked issues format |
| `IssueLinkTypeProvider` | Provides available link types from JIRA |

### API Library

Uses `lesstif/php-jira-rest-client` library for JIRA API communication.

### IssueField Building

The `IssueFieldFactory` uses `JiraRestApi\Issue\IssueField` to build issue data:

```php
$issueField = new IssueField();
$issueField
    ->setProjectKey('PROJ')
    ->setSummary('Issue summary')
    ->setDescription('Issue description')
    ->setIssueTypeAsString('Task')
    ->setAssigneeNameAsString('username')
    ->setPriorityNameAsString('High')
    ->addComponentsAsArray(['Component1'])
    ->addLabelAsString('label1');
```
