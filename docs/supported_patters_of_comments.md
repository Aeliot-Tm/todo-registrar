# Supported formats of comments

The tool detects configured tags (default: `TODO`, `FIXME`; case-insensitive) in PHP and YAML source files.

## Supported file types

| File type | Comment styles |
|---|---|
| PHP (`.php` and [aliased extensions](config/general_config_php.md#option-extensionaliases) such as `.module`) | `//`, `#`, `/* */`, `/** */` |
| YAML (`.yaml`, `.yml`) | `#` single-line comments only |

Configure scanned paths and extensions in [general config](config/general_config.md).
For how files are parsed, see [Processing flow](processing_flow.md).

## Tag patterns

1. Tag and summary separated by a colon:
   ```php
   // TODO: comment summary
   ```
2. Tag and summary without a colon:
   ```php
   // TODO comment summary
   ```
3. Tag with assignee and colon:
   ```php
   // TODO@assignee: comment summary
   ```
4. Tag with assignee without a colon:
   ```php
   // TODO@assignee comment summary
   ```
5. Multiline description in a block or doc comment. Lines after the first tag line must use the same indentation
   as the summary text (aligned with the colon, assignee, or tag). Lines that break this rule are not part of the TODO description.
   ```php
   /**
    * TODO: comment summary
    *       and some complex description
    *       which must have indentation same as the summary start.
    * This line will not be detected as part of the TODO
    * because it does not have the expected indentation.
    */
   ```

### Sequential Comments Gluing

Consecutive single-line comments (`//` or `#`) can be glued into one multiline comment when [sequential comments gluing](sequential_comments_gluing.md) is enabled.

```php
// TODO: Implement authentication
//       - Add login form
//       - Validate credentials
```

Without gluing (default) only the first line is processed as a TODO.
With gluing enabled all three lines are processed as one TODO comment.


#### YAML example

```yaml
services:
  app.mail:
    # TODO: configure SMTP credentials for production
    #       see infrastructure ticket for host and port
    transport: smtp
```

In YAML, only `#` comments are supported. Multiline descriptions follow the same indentation rules as in PHP when [sequential comments gluing](sequential_comments_gluing.md) is enabled.

### Assignee-part

An assignee is a username attached to the tag with `@`. It matches `/[a-z0-9._-]+/i` and is passed to the registrar as an assignee identifier.

### Inline configuration

A comment can contain [inline configuration](inline_config.md) in a `{EXTRAS: {...}}` block, usually as the last part of the description.

## Injection of issue key

After registration, the issue key is injected before the comment summary (after assignee and colon when present). TODOs that already contain a recognized key are skipped on the next run.

**Examples after processing:**

```php
// TODO: PROJ-123 comment summary
// TODO PROJ-123 comment summary
// TODO@assignee: PROJ-123 comment summary
// TODO@assignee PROJ-123 comment summary
```

```php
/**
 * TODO: PROJ-123 comment summary
 *       and some complex description
 */
```

For position and separator options, see [Issue key injection](issue_key_injection.md).
