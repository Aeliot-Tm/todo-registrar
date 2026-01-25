# Context Display

The `showContext` option allows you to include information about the code context where a TODO comment is located in the issue description.
This helps to understand the exact location and surrounding code structure without opening the file.


## Configuration

The `showContext` option can be configured in the registrar's `issue` section.

### YAML Configuration

```yaml
registrar:
  type: GitHub
  options:
    issue:
      showContext: true             # Default format (code_block)
```

### PHP Configuration

```php
$config->setRegistrar('GitHub', [
    'issue' => [
        'showContext' => true,      // Default format (code_block)
    ],
]);
```

### Values
d
| Value | Data Type | Description |
|---|---|---|
| `false` | bool | (default) - context is not displayed |
| `true` | bool | context is displayed in the default format (`code_block`) |
| `arrow_chained` | string | context is displayed in arrow-chained format (one line) |
| `code_block` | string | context is displayed in code block format (multi-line) |
| `number_sign` | string | context is displayed as numbered list (multi-line) |
| `numbered` | string | context is displayed as numbered list (multi-line) |

## Output Formats

### Arrow Format (`'arrow_chained'`)

Displays context as a single line with arrows (`->`) separating each level:

**Example:**
```
User needs to verify email before updating profile

File: /app/src/Service/UserService.php -> Namespace: App\Service -> Class: UserService -> Method: updateUser()
```

This format is compact and useful when you want to save space in the issue description.

### Code Block Format (`'code_block'`)

Displays context as a formatted code block with each level on a separate line:

**Example:**
````
User needs to verify email before updating profile

```
File: /app/src/Service/UserService.php
Namespace: App\Service
Class: UserService
Method: updateUser()
```
````

### Code Block Format (`'number_sign'`)

Displays context as a list with number sign symbol as prefix for each line:

**Example:**
```
User needs to verify email before updating profile

# File: /app/src/Service/UserService.php
# Namespace: App\Service
# Class: UserService
# Method: updateUser()
```

### Code Block Format (`'numbered'`)

Displays context as a formatted numbered list:

**Example:**
```
User needs to verify email before updating profile

1. File: /app/src/Service/UserService.php
2. Namespace: App\Service
3. Class: UserService
4. Method: updateUser()
```

This format is more readable and useful when you need clear visual separation of context levels.

## Context Information

The context display includes the following information depending on where the TODO comment is located:

| Context Type | Description | Example |
|--------------|-------------|---------|
| File | File path | `File: /app/src/Service/UserService.php` |
| Namespace | PHP namespace | `Namespace: App\Service` |
| Class | Class name (or `{anonymous}` for anonymous classes) | `Class: UserService` |
| Interface | Interface name | `Interface: UserInterface` |
| Trait | Trait name | `Trait: LoggerTrait` |
| Enum | Enum name | `Enum: Status` |
| Enum case | Enum case name | `Enum case: Active` |
| Method | Method name | `Method: updateUser()` |
| Function | Function name | `Function: helper()` |
| Closure | Anonymous function | `Closure` |
| Arrow function | Arrow function | `Arrow function` |
| Match expression | Match expression | `Match expression` |
| Property | Property name | `Property: email` |
| Parameter | Parameter name | `Parameter: userId` |
| Constant | Class constant name | `Constant: MAX_SIZE` |

## Notes

- Context is only added when the TODO implements `ContextAwareTodoInterface`
- Context is prepended to the issue description with two blank lines separation
- The context path is built from the outermost level (file) to the innermost level (where TODO is located)
- Anonymous classes are displayed as `{anonymous}`
- Unknown property/parameter names are displayed as `{unknown}`

## See Also

- [General Config](config/general_config.md) - Global configuration options
- [Inline Config](inline_config.md) - Configure individual TODOs
