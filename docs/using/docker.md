## Using with Docker Container

You can use the pre-built Docker image from GitHub Container Registry:

1. Pull the latest image
   ```shell
   docker pull ghcr.io/aeliot-tm/todo-registrar:latest
   ```
2. Run script with necessary [command line options](../command_line_options.md)
   ```shell
   docker run --rm -it \
     -v $(pwd):/code \
     ghcr.io/aeliot-tm/todo-registrar:latest <options>
   ```

**Important notes:**
- Mount your project directory to `/code` (this is the working directory inside the container).
- Use `-it` flags for interactive mode if you need to see real-time output.
- The container uses unbuffered output, so messages will appear in real-time.
- Pass necessary environment variables instructions `-e $VAR_NAME`
  or by the creating of `.env` file with necessary variables in the root of project.
- PHAR file inside the container (`/usr/local/bin/todo-registrar`). You can rely on it.
