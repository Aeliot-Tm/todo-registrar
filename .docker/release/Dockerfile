FROM php:8.2-cli

RUN set -xe; \
    apt-get update && \
    apt-get install -y --no-install-recommends coreutils && \
    rm -rf /var/lib/apt/lists/*

RUN mkdir /code

COPY --chmod=755 todo-registrar.phar /usr/local/bin/todo-registrar

WORKDIR /code

ENTRYPOINT ["stdbuf", "-o0", "-e0", "php", "-d", "output_buffering=0", "-d", "implicit_flush=1", "/usr/local/bin/todo-registrar"]
