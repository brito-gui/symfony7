# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework, with full [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 support

## Getting Started

1. Clone this repository `git clone git@github.com:brito-gui/symfony7.git`
2. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
3. Run `docker-compose up` to start the project
4. Run `docker exec -it symfony7_app_1 bash` to get inside the `app` container
5. Inside the container, run `composer install` to get your vendor ready
6. Inside the container, run `bin/console doctrine:schema:update --force` to get your database ready
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
