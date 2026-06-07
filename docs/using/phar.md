### Using of PHAR file

1. Download PHAR directly to root directory
   ```shell
   wget -O todo-registrar.phar "https://github.com/Aeliot-Tm/todo-registrar/releases/latest/download/todo-registrar.phar"
   ```
2. Call script with necessary [command line options](../command_line_options.md)
   ```shell
   php todo-registrar.phar <options>
   ```

Additional instructions how to verify package read [here](../installation/phar_directly.md).

**Alternatively**, you can install `phar` file by [PHIVE](https://phar.io/)
1. Install phar file (by default it will be installed in directory `tools` in the root of project without extension)
   ```shell
   phive install todo-registrar
   ```
2. Call script with necessary [command line options](../command_line_options.md)
   ```shell
   tools/todo-registrar <options>
   ```

Additional instructions read [here](../installation/phive.md).
