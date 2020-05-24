# Green Web Foundation API

In this repo you can find the source code for the API and checking code that the Green Web Foundation servers use to check the power a domain uses.

[![Build Status](https://travis-ci.com/thegreenwebfoundation/thegreenwebfoundation.svg?branch=master)](https://travis-ci.com/thegreenwebfoundation/thegreenwebfoundation)
## Overview

Following [Simon Brown's C4 model](http://c4model.com/) this repo includes the API server code, along with the green check worker code in `packages/greencheck`.

![API](./docs/img/containers-api.jpg)

# Apps - API Server at [api.thegreenwebfoundation.org](https://api.thegreenwebfoundation.org/)

This repository contains the code served to you when you visit http://api.thegreenwebfoundation.org.

When requests come in, symfony accepts and validates the request, and creates a job for enqeueue to service with a worker.

![API](./docs/img/components-api-server.jpg)

The greenweb api application running on https://api.thegreenwebfoundation.org

This provides a backend for the browser extensions and the website on https://www.thegreenwebfoundation.org

This needs:

- an enqueue adapter, like fs for development, amqp for production
- php 7.3
- nginx
- redis for greencheck library
- ansible and ssh access to server for deploys

Currently runs on symfony 5.x

To start development:

- Clone the monorepo `git clone git@github.com:thegreenwebfoundation/thegreenwebfoundation.git`
- Configure .env.local (copy from .env) for a local mysql database
- `composer install`
- `bin/console server:run`
- check the fixtures in packages/greencheck/src/TGWF/Fixtures to setup a fixture database

To deploy:

- `bin/deploy`

To test locally:

- Go to http://127.0.0.1:8000 for homepage
- Go to http://127.0.0.1:8000/greencheck/www.nu.nl to test www.nu.nl
- If this keeps loading, everything is correctly setup, Now run `bin/console enqueu:consume` in a seperate terminal to process the checks


# Packages - Greencheck

In `packages/greencheck` is the library used for carrying out checks against the Green Web Foundation Database. Workers take jobs in a RabbitMQ queue, and call the greencheck code to return the result quickly, before passing the result, RPC-style to the original calling code in symfony API server.


![API](./docs/img/components-api-worker.jpg)

# Packages - public suffix

In `packages/publicsuffix` is a library provides helpers for retrieving the public suffix of a domain name based on the Mozilla Public Suffix list. Used by the API Server.