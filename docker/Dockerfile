FROM php:8.4-cli

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      git \
      unzip; \
    rm -rf /var/lib/apt/lists/*; \
    if ! getent group "${HOST_GID}" >/dev/null; then \
        groupadd --gid "${HOST_GID}" app; \
    fi; \
    useradd \
        --uid "${HOST_UID}" \
        --gid "${HOST_GID}" \
        --create-home \
        app; \
    mkdir -p /home/app/.cache/composer; \
    chown -R "${HOST_UID}:${HOST_GID}" /home/app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV HOME=/home/app

USER app

CMD ["bash"]
