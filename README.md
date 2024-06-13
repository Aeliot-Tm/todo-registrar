# TODO Registrar

Package responsible for registration of issues in Issue Trackers.

## Installation

1. Require package via Composer:
   ```shell
   composer require --dev aeliot/todo-registrar
   ```
2. Create configuration file. It expects ".todo-registrar.php" or ".todo-registrar.dist.php" at the root of the project.

## Using

1. Call script:
   ```shell
   vendor/bin/todo-registrar
   ```
   You may pass option with it `--config=/custom/path/to/config`. Otherwise, it tries to use one of default files. 
2. Commit updated files. You may config your pipeline/job on CI which commits updates.

## Configuration file

Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`. See [example](.todo-registrar.dist.php).

It has setters:
1. `setFinder` - accepts instance of configured finder of php-files.
2. `setRegistrar` - responsible for configuration of registrar factory. It accepts as type of registrar with its config
   as instance of custom registrar factory.
3. `setTags` - array of detected tags. It supports "todo" and "fixme" by default. 
   You don't need to configure it when you want to use only this tags. Nevertheless, you have to set them 
   when you want to use them together with your custom tags.

### Supported patters of comments (examples):

It detects TODO-tags in single-line comments started with both `//` and `#` symbols
and multiple-line comments `/* ... */` and phpDoc `/** ... **/`.

1. Tag and comment separated by semicolon
   ```php
   // TODO: comment summary
   ```
2. Tag and comment does not separated by semicolon
   ```php
   // TODO comment summary
   ```
3. Tag with assignee and comment separated by semicolon
   ```php
   // TODO@assigne: comment summary
   ```
4. Tag with assignee and comment does not separated by semicolon
   ```php
   // TODO@assigne comment summary
   ```
5. Multiline comment with complex description. All lines after the first one with tag MUST have indentation
   same to the text of the first line. So, all af them will be detected af part of description of TODO.
   Multiple line comments may have assignee and semicolon same as single-line comments/.
   ```php
   /**
    * TODO: comment summary
    *       and some complex description
    *       which must have indentation same as end of one presented:
    *       - semicolon
    *       - assignee
    *       - tag
    *       So, all this text will be passed to registrar as description
    *       without not meaning indentations (" *      " in this case).
    * This line (and all after) will not be detected as part (description) of "TODO"
    * case they don't have expected indentation.
    */
   ```

As a result of processing of such comments, ID of ISSUE will be injected before comment summary
and after semicolon and assignee when they are presented. For example:
1. Tag and comment separated by semicolon
   ```php
   // TODO: XX-001 comment summary
   ```
2. Tag and comment does not separated by semicolon
   ```php
   // TODO XX-001 comment summary
   ```
3. Tag with assignee and comment separated by semicolon
   ```php
   // TODO@assigne: XX-001 comment summary
   ```
4. Tag with assignee and comment does not separated by semicolon
   ```php
   // TODO@assigne XX-001 comment summary
   ```
5. Multiline comment with complex description. All lines after the first one with tag MUST have indentation
   same to the text of the first line. So, all af them will be detected af part of description of TODO.
   Multiple line comments may have assignee and semicolon same as single-line comments/.
   ```php
   /**
    * TODO: XX-001 comment summary
    *       and some complex description
    */
   ```

### Assignee-part

It is some "username" which separated of tag by symbol "@". It sticks to pattern `/[a-z0-9._-]+/i`.
System pass it in payload to registrar with aim to be used as "identifier" of assignee in issue tracker.

## Supported Issue Trackers

Currently, todo-registrar supports the following issue trackers:

| Issue Tracker                                   | Description              |
|-------------------------------------------------|--------------------------|
| [Jira](https://www.atlassian.com/software/jira) | Supported via API tokens |
