FROM php:8.2-cli

RUN mkdir /code

COPY --chmod=755 todo-registrar.phar /usr/local/bin/todo-registrar

WORKDIR /code

ENTRYPOINT ["php", "/usr/local/bin/todo-registrar"]
