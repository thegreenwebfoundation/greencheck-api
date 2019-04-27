FROM ubuntu

# we're prompted if we don't add this
ENV DEBIAN_FRONTEND=noninteractive

## install the deps for running greenchecks
RUN apt-get update \
  && apt-get install git \
  php-fpm php-pear php7.2-dev php7.2-mbstring php-mysql \
  zip unzip  --yes \
  && pecl install redis-4.0.1

# so we can sanity check the box
RUN apt-get install mysql-client --yes

# # fetch the composer image, and copy the composer binary
# # so we can run composer later in our container
COPY --from=composer /usr/bin/composer /usr/bin/composer

# # set up the src
RUN mkdir -p /app

# # move
COPY packages/greencheck/composer.json /app/composer.json
WORKDIR /app/
RUN /usr/bin/composer install

COPY packages/greencheck /app/

# RUN ./bin/phpunit -c phpunit.xml.dist