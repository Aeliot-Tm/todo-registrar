# Configuration PHP file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
If you put it to another place, you have to define path to it while the calling of script
by option `--config=/path/to/cofig`.

Config file have to returns instance of interface `Aeliot\TodoRegistrarContracts\GeneralConfigInterface`.

You may implement it yourself (read about [customization](../customization.md))
or use existing class `Aeliot\TodoRegistrar\Config`.

At least, you have to set configured `finder` and define registrar with its options.

See [example](../../examples/JIRA/.todo-registrar.php).

> **NOTE:** When you work with PHAR file you can use trick for the autoloading of classes:
> ```php
> \Phar::loadPhar(__DIR__.'/todo-registrar.phar', 'todo-registrar.phar');
> require_once 'phar://todo-registrar.phar/vendor/autoload.php';
> ```

## Required configurations

The main configurations are:
- [finder](#setting-of-finder)
- [registrar](#setting-of-registrar)
- and probably [tags](#setting-of-tags)

For others read [customization](../customization.md) section.

### Setting of Finder

Pass instance of `Aeliot\TodoRegistrarContracts\FinderInterface` to method `setFinder`.
If you use implementation from this project (`Aeliot\TodoRegistrar\Service\File\Finder`)
then read documentation of [Symfony Finder](https://symfony.com/doc/current/components/finder.html).
It has the same configuration.

### Setting of Registrar

Method `setRegistrar` is responsible for configuration of registrar factory.

It accepts two arguments:
1. First one is registrar type (`Aeliot\TodoRegistrar\Enum\RegistrarType` or its string value)
   or instance of registrar factory (`Aeliot\TodoRegistrarContracts\RegistrarFactoryInterface`).
2. Second one is array of config for registrar. For example, read about [JIRA configuration](../registrar/JIRA/config.md).

So, you can use build-in registrar or pass your own.

### Setting of Tags

Permit to define array of tags to be detected.

Script supports `TODO` and `FIXME` by default.
You don't need to configure it when you want to use only this tags. Nevertheless, you have to set them
when you want to use them together with your custom tags.

Don't wary about case of tags. They will be found in case-insensitive mode.
