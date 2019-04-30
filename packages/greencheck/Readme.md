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

There is also experimental docker support.
