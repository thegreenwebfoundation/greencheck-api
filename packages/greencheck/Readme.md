# The greencheck package

This package provides the sitecheck classes and doctrine database backend entity's for running greenchecks.
It is included in the api and admin apps, in most cases as part of a worker.

### Installation

If you want to run tests that use Redis as a cache, make sure you have the redis extension for PHP installed:

```
pecl install redis
```

## Running the tests

```
bin/phpunit -c configuration.xml
```

If you want to run all the tests without stopping when a test fails, change `stopOnFailure="true"` to stopOnFailure="false" in `phpunit.xml.dist`.

## Docker Support

There is also experimental docker support.

Make sure you have Docker installed.

Next make sure you have the correct `config.yml` file, set up, with the appropriate values.

If you're _not_ using docker, and you have Redis and MySQL installed on your host machine, your config should look like so, so you connect to the local running Redis and MySQL servers:

#### What non-docker config looks like:

```
greencheck:
  db:
    driver: pdo_mysql
    host: localhost
    user: your_db_user
    password:
    dbname: your_database_name
  redis:
    host: localhost
```

#### What a docker config looks like

Because docker compose represents Redis and MySQL servers as separate services that the greencheck code connects to, you need to make sure this is reflected in the config file. Specifically, we set the hostnames to use `redis` and `db` matching the services in the `docker-compose.yml` file, and use the credentials the docker image expects by default :

```yaml
greencheck:
  db:
    driver: pdo_mysql
    host: db
    user: root
    password:
    dbname: circle_test
  redis:
    host: redis
```

#### Spinning up docker

To spin up the set of services, use `docker-compose` up, to fetch the images, and start runnign them. Add the `-d` flag to make it run in the background:

```
docker-compose up -d
```

Once you have the containers running, you can access the container with the code by 'shelling into' the relevant container:

```
docker-compose run web bash
```

You can spin down the set of services with `docker-compose down` too:

```
docker-compose down
```
