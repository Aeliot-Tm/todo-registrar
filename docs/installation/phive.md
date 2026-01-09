# Installation with PHIVE

You can install this package with [PHIVE](https://phar.io/). It permits you to install package by one console command
without extending dependencies in your composer-files.

Basically, it's enough to call command (it expects that `phive` is installed global):
```shell
phive install todo-registrar
```

Sometimes you may need to update database of package-aliases of PHIVE. See [issue #3](https://github.com/Aeliot-Tm/php-cs-fixer-baseline/issues/3)
So, just call console command for it:
```shell
phive update-repository-list
```

To upgrade this package use the following command:
```shell
phive update todo-registrar
```
