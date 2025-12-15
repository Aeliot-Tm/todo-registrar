FROM php:8.2-cli

RUN mkdir /code

COPY --chmod=755 todo-registrar.phar /usr/local/bin/todo-registrar

WORKDIR /code

ENTRYPOINT ["php", "-d", "output_buffering=0", "-d", "implicit_flush=1", "/usr/local/bin/todo-registrar"]
