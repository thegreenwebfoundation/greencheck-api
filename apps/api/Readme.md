The greenweb api application running on https://api.thegreenwebfoundation.org

This provides a backend for the browser extensions and the website on https://www.thegreenwebfoundation.org

This needs:

- an enqueue adapter, like fs for development, amqp for production
- php 7.2
- nginx
- memcache/redis for greencheck library
- ansible and ssh access to server for deploys

Currently runs on symfony 4.x

To start development:

- Clone the monorepo `git clone git@github.com:thegreenwebfoundation/thegreenwebfoundation.git`
- `cd apps/api`
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