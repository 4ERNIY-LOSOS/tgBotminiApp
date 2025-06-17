# Toaster

[![HLEB2](https://img.shields.io/badge/HLEB-2-darkcyan)](https://github.com/phphleb/hleb) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)

A template for local development using the [HLEB2](https://github.com/phphleb/hleb) framework.

## Recipe

- [Docker](https://www.docker.com)
- this repository
- `docker-compose up -d`

## Composition

<details>
  <summary>Development repository</summary>

  After launching the containers, the `hleb` directory will be created in the root of the project
  with the new [HLEB2](https://packagist.org/packages/phphleb/hleb) project.
</details>

<details>
  <summary>Local server</summary>

  Default [localhost:5125](http://localhost:5125).
  If you are not satisfied with the port, change `SERVER_EXTERNAL_PORT` in the `.env` file.
</details>

<details>
  <summary>MariaDB</summary>

  [About MariaDB](https://mariadb.org/)  
  In the new project `hleb` the file will be automatically created
  `/config/database-local.php` with the configuration for connecting to the DBMS.
</details>

<details>
  <summary>phpMyAdmin</summary>

  [About phpMyAdmin](https://www.phpmyadmin.net/)  
  Default [localhost:8080](http://localhost:8080).
  Authorization is automatic.
  If you are not satisfied with the port, change `PMA_EXTERNAL_PORT` in the `.env` file.
</details>

<details>
  <summary>Xdebug</summary>

  [About Xdebug](https://xdebug.org/)  
  The configuration file is `docker/xdebug.ini`.
  The default port is `9003`.
  In `docker-compose.yml` the server is specified as `serverName`.
  Defaults to `serverName=toaster`.
</details>

<details>
  <summary>PHP Coding Standards Fixer</summary>

  [About PHP CS Fixer](https://cs.symfony.com/)  
  The [configuration](https://cs.symfony.com/doc/config.html) from `docker/.php-cs-fixer.php` is copied to `/hleb`.
  Cheat sheet on the rules [here](https://mlocati.github.io/php-cs-fixer-configurator/#version:3.7).
  After creating a new project, it automatically edits files using rules.
</details>

## HLOGIN
User authorization module.

[About HLOGIN](https://github.com/phphleb/hlogin)

Not installed by default, but can be easily added to your project.
Connect to the `php` service container and execute `./add-hlogin.sh`.

During installation, you will need to answer three questions from the system:

1. Preferred interface style.
2. Administrator's email.
3. Administrator password.
