## Inline Configuration

Script supports inline configuration of each TODO-comment. It helps flexibly configure different aspects of created issues.
Like relations to other issues, labels, components and so on.

Very flexible tool. A format similar to js objects or JSON, only without quotes.
It is so-called "EXTRAS" case they started with `{EXTRAS: ...`. It should be a part of multi-line commit
and indented a description ([see supported formats](supported_patters_of_comments.md)).
The system expects no more than one inline config per TODO. And it would be nice to keep them as last part of TODO-commit.

It may be split to multiple lines. But don't forget about indents of each line.

```php
/**
 * TODO@an.assignee: summary of issue (title)
 *                   And some complex description on second line and below.
 *                   Fell you free to add any complex description as you wish.
 *                        But don't forget to start each line on same column as summary (title) or later.
 *                   And suit same rule for EXTRAS, which can be split to multiple lines with any spaces.
 *                   See below.
 *                   {EXTRAS: {
 *                      someKey: [value1, value2],
 *                      moreComplexData: {key1: [v3], key2: {
 *                            neverMindHowManyLevels: [v4]
 *                      }}
 *                   }}
 */
```

### Multi-word Keys and Values

You can use double quotes for multi-word keys and values, following JSON syntax:

```php
/**
 * TODO: summary
 *       {EXTRAS: {
 *          labels: ["bug fix", "high priority", "needs review"],
 *          "custom field": "some value"
 *       }}
 */
```

Supported escape sequences (JSON-compliant):
- `\"` - double quote
- `\\` - backslash
- `\/` - forward slash
- `\n` - newline
- `\r` - carriage return
- `\t` - tab
- `\b` - backspace
- `\f` - form feed

You can mix quoted and unquoted values:

```php
{EXTRAS: {labels: [simple-label, "complex label with spaces"]}}
```

### Examples

Below are examples of settings supported by the implemented JIRA-registrar.

1. Do you need to provide a related ticket? Easily.
   ```
   {EXTRAS: {linkedIssues: XX-123}}
   ```
2. Do you need to link multiple tickets? So:
   ```
   {EXTRAS: {linkedIssues: [XX-123, XX-234]}}
   ```
3. Do you need to link tickets with different link types? Easily.
   ```
   {EXTRAS: {linkedIssues: {child_of: XX-123, is_blocked_by: [XX-234, YY-567]}}}
   ```
4. Labels and components can be provided in the same way.
   ```
   {EXTRAS: {labels: [label-a, label-b], components: [component-a, component-b]}}
   ```

### Common keys supported by all registrars

The following inline config keys are supported by all registrars:

| Key | Description |
|---|---|
| assignee | Identifier of user to assign to the issue (string or array) |
| assignees | Same as `assignee`. Both keys are supported |
| contextTitle | Title of context path. Overrides `contextTitle` from general config |
| labels | List of labels/tags which will be assigned to the issue |
| showContext | Include code context in issue description. Overrides `showContext` from general config |

### Inline configs specific for issue trackers

1. [GitHub](registrar/GitHub/config.md#inline-config)
2. [GitLab](registrar/GitLab/config.md#inline-config)
3. [JIRA](registrar/JIRA/config.md#inline-config)
4. [Redmine](registrar/Redmine/config.md#inline-config)
5. [Yandex Tracker](registrar/YandexTracker/config.md#inline-config)


## The order of applying of configs

When the same field can be set from multiple sources, values are applied with the following priority
(highest to lowest). This order applies to all registrars:

1. **Tag assignee** — `@assignee` joined to TODO-tag (e.g. `TODO@john`)
2. **Inline config** — values from `{EXTRAS: {...}}`
3. **General config** — values from the [general config file](config/general_config.md)
