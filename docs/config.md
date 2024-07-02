# Configuration file

It expects that file `.todo-registrar.php` or `.todo-registrar.dist.php` added in the root directory of project.
It may be put in any other place, but you have to define path to it when call the script with option `--config=/path/to/cofig`.
Config file is php-file which returns instance of class `\Aeliot\TodoRegistrar\Config`. See [example](../.todo-registrar.dist.php).

It has setters:
1. `setFinder` - accepts instance of configured finder of php-files.
2. `setRegistrar` - responsible for configuration of registrar factory. It accepts as type of registrar with its config
   as instance of custom registrar factory.
3. `setTags` - array of detected tags. It supports "todo" and "fixme" by default.
   You don't need to configure it when you want to use only this tags. Nevertheless, you have to set them
   when you want to use them together with your custom tags.

