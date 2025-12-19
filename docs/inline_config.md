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

### Inline configs specific for issue trackers

1. [GitHub](registrar/github/config.md#inline-config)
2. [GitLab](registrar/gitlab/config.md#inline-config)
3. [JIRA](registrar/jira/config.md#inline-config)


## The order of applying of configs
1. `@assignee` joined to TODO-tag.
2. `EXTRAS`
3. Then the [general config](config/global_config_php.md) of the JIRA registrar.
