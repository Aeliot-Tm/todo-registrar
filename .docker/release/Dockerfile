FROM php:8.2-cli

RUN mkdir /code
RUN ln -s /github/workspace /code

COPY todo-registrar.phar /usr/local/bin/todo-registrar
RUN chmod +x /usr/local/bin/todo-registrar

WORKDIR /code

ENTRYPOINT ["php", "/usr/local/bin/todo-registrar"]
