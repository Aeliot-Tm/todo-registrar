# Configuration file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
It may be put in any other place, but you have to define path to it when call the script with option `--config=/path/to/cofig`.

Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`.
See [example](../../examples/JIRA/.todo-registrar.php).

## Methods

| Method                 | Is Required |
|------------------------|-------------|
| setFinder              | yes         |
| setInlineConfigFactory | no          |
| setInlineConfigReader  | no          |
| setRegistrar           | yes         |
| setTags                | no          |


### setFinder

Accepts instance of configured finder (`\Aeliot\TodoRegistrar\Service\File\Finder`) responsible for finding of php-files.
Very similar to configuration of Finder for "PHP CS Fixer".

### setInlineConfigFactory

Accepts instance of `\Aeliot\TodoRegistrar\InlineConfigFactoryInterface`

You can implement and expects instance of your custom inline config in your registrar.
This method permits to provide factory for it.

### setInlineConfigReader

Accepts instance of `\Aeliot\TodoRegistrar\InlineConfigReaderInterface`.

So, you can use your own reader of inline config which support your preferred format or relay on build-in.

### setRegistrar

Responsible for configuration of registrar factory.

It accepts two arguments:
1. First one is registrar type (`\Aeliot\TodoRegistrar\Enum\RegistrarType`)
   or instance of registrar factory (`\Aeliot\TodoRegistrar\Service\Registrar\RegistrarFactoryInterface`).
2. Second one is array of config for registrar. See [example for JIRA](../registrar/jira/config.md).

So, you can use build-in registrar or pass your own.

### setTags

Permit to define array of tags to be detected.

Script supports `TODO` and `FIXME` by default.
You don't need to configure it when you want to use only this tags. Nevertheless, you have to set them
when you want to use them together with your custom tags.

Don't wary about case of tags. They will be found in case-insensitive mode.
