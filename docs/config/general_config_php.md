# Configuration PHP file

> There is described php-form of [general config file](general_config.md).

Config file have to returns instance of interface `Aeliot\TodoRegistrarContracts\GeneralConfigInterface`.

You may implement it yourself (read about [customization](../customization.md))
or use existing class `Aeliot\TodoRegistrar\Config`.

**At least, you have to configure finder and registrar**

See [example](../../examples/JIRA/.todo-registrar.php):
```php
use Aeliot\TodoRegistrar\Config;
use Aeliot\TodoRegistrar\Service\File\Finder;

return (new Config())
    ->setFinder((new Finder())->in(__DIR__))
    ->setRegistrar('JIRA', [
        'issue' => [
            'projectKey' => 'TODO',
            'type' => 'Bug',
            // add optional configs
        ],
        'service' => [
            'host' => $_ENV['JIRA_HOST'],
            'personalAccessToken' => $_ENV['JIRA_PERSONAL_ACCESS_TOKEN'],
            'tokenBasedAuth' => true,
        ],
    ]);
```

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
2. Second one is array of config for registrar ('registrar options').

So, you can use build-in registrar or pass your by using `RegistrarFactoryInterface`.

Registrar options specific for each issue tracker see in separate documentation:

1. [GitHub](../registrar/GitHub/config.md)
2. [GitLab](../registrar/GitLab/config.md)
3. [JIRA](../registrar/JIRA/config.md)
4. [Redmine](../registrar/Redmine/config.md)
5. [Yandex Tracker](../registrar/YandexTracker/config.md)

### Setting of Tags

Permit to define array of tags to be detected.

Script supports `TODO` and `FIXME` by default.
You don't need to configure it when you want to use only this tags. Nevertheless, you have to set them
when you want to use them together with your custom tags.

Don't wary about case of tags. They will be found in case-insensitive mode.
