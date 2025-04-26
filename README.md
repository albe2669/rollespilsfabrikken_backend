# Rollespilsfabrikken backend

This repo serves as the backend for the Rollespilsfabrikkens forum

## Table of contents
<!-- TOC -->
* [Rollespilsfabrikken backend](#rollespilsfabrikken-backend)
  * [Table of contents](#table-of-contents)
  * [Setup, requirements, etc](#setup-requirements-etc)
    * [Requirements](#requirements)
    * [Setup and install](#setup-and-install)
      * [Database](#database)
      * [IDE integration](#ide-integration)
    * [Running the app](#running-the-app)
    * [Skipping all of this when syncing](#skipping-all-of-this-when-syncing)
    * [Pre-commit hooks](#pre-commit-hooks)
  * [Frontend deployment](#frontend-deployment)
<!-- TOC -->

## Setup, requirements, etc
### Requirements
- PHP 8.4 or above
- Composer matching the PHP version
- Docker

Apart from docker the dependencies can all be installed using Nix using the `flake.nix` file in the root of the repo. To do this, run the following command:

```bash
nix develop .
```

### Setup and install
First install the PHP and node dependencies using composer. You can do this by running the following command:

```bash
composer install
```

After installing the requirements, you must configure the project, first copy the `.env.example` file to `.env` and set the `APP_KEY` variable. You can do this by running the following command:

```bash
make init
```

Most of the app will work with the default config after that, but, some features like the Linear and Sharepoint integrations won't work. For those setup the relevant API keys and tokens in the `.env` file.

#### Database
The app uses a MySQL database, you can set up a local database and UI using docker compose. The `docker-compose.yml` uses the keys in the `.env` file for easy configuration. To start the database, run the following command:

```bash
docker compose up -d
```

Then run the migrations to set up the database:
```
php artisan migrate
```

#### IDE integration
If you are using PhpStorm, you can set up the IDE integration by running the following command:

```bash
make ide
```

### Running the app
Now simply run the following command to start the app:

```bash
composer dev
```

### Skipping all of this when syncing
If you are syncing the repo and don't want to set up the whole thing, you can run the following command to skip all of this. It will install, build, migrate and IDE integrate:

```bash
make sync
```

### Pre-commit hooks
The repo uses pre-commit hooks to run the linters and formatters before committing. You can set them up by running the following command:

```bash
make hooks
```


## Frontend deployment
First build the frontend locally, using node version 16: https://github.com/avborup/rollespilsfabrikken-forum-calendar-frontend

Then logon to the Simply.com dashboard and navigate to the forum.rollespilsfabrikken/public folder, here you shall insert the contents of the dist folder of the build.

This can be done with FTP and with a zip file.

After the above copy the contents of the built index.html file into welcome.blade.php in the forum.rollespilsfabrikken.dk/resources/views folder.

Go to SSH, navigate to forum.rollespilsfabrikken.dk folder and run `php artisan view:cache`

Deployed!
