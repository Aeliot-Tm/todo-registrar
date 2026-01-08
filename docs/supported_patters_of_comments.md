# Supported formats and patterns of comments

It detects TODO-tags in single-line comments started with both `//` and `#` symbols
and multiple-line comments `/* ... */` and phpDoc `/** ... **/`.

1. Tag and comment separated by colon
   ```php
   // TODO: comment summary
   ```
2. Tag and comment does not separated by colon
   ```php
   // TODO comment summary
   ```
3. Tag with assignee and comment separated by colon
   ```php
   // TODO@assigne: comment summary
   ```
4. Tag with assignee and comment does not separated by colon
   ```php
   // TODO@assigne comment summary
   ```
5. Multiline comment with complex description. All lines after the first one with tag MUST have indentation
   same to the text of the first line. So, all af them will be detected af part of description of TODO.
   Multiple line comments may have assignee and colon same as single-line comments/.
   ```php
   /**
    * TODO: comment summary
    *       and some complex description
    *       which must have indentation same as end of one presented:
    *       - colon
    *       - assignee
    *       - tag
    *       So, all this text will be passed to registrar as description
    *       without not meaning indentations (" *      " in this case).
    * This line (and all after) will not be detected as part (description) of "TODO"
    * case they don't have expected indentation.
    */
   ```


### Assignee-part

It is some "username" which separated of tag by symbol "@". It sticks to pattern `/[a-z0-9._-]+/i`.
System pass it in payload to registrar with aim to be used as "identifier" of assignee in issue tracker.

### Inline Configuration

Comment can contain [inline configuration](inline_config.md).

## Injection of Issue ID

As a result of processing of such comments, **ID of ISSUE will be injected before comment summary**
(after assignee and colon when they are presented).

**For example:**

1. Tag and comment separated by colon
   ```php
   // TODO: XX-001 comment summary
   ```
2. Tag and comment does not separated by colon
   ```php
   // TODO XX-001 comment summary
   ```
3. Tag with assignee and comment separated by colon
   ```php
   // TODO@assigne: XX-001 comment summary
   ```
4. Tag with assignee and comment does not separated by colon
   ```php
   // TODO@assigne XX-001 comment summary
   ```
5. Multiline comment with complex description. All lines after the first one with tag MUST have indentation
   same to the text of the first line. So, all af them will be detected af part of description of TODO.
   Multiple line comments may have assignee and colon same as single-line comments/.
   ```php
   /**
    * TODO: XX-001 comment summary
    *       and some complex description
    */
   ```
