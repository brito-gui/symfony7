# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 support

## Getting Started

1. Clone this repository `git clone git@github.com:brito-gui/symfony7.git`
2. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
3. Run `docker compose up -d` to start the project
4. Get your vendor ready
   ```
    docker compose exec app bash -c 'composer install'
   ```
5. We need to generate the public and private keys used for signing JWT tokens
   ```
    docker compose exec app bash -c '
       set -e
       apt-get install openssl
       php bin/console lexik:jwt:generate-keypair
       setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
       setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
   '
   ```
6. Get your database ready
   ```
    docker compose exec app bash -c '
       bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --all-or-nothing
    '
   ```
7. Outside the container, run `chown -R ${USERNAME}:${USERNAME} .`
8. Open `http://127.0.0.1:21000/api` in your favorite web browser
9. Run `docker-compose down --remove-orphans` to stop the Docker containers.

## Features

* MySQL
* Composer
* Memcached
* Docker
* MakerBundle
* Doctrine
* ApiPlatform
* JWT Authentication
