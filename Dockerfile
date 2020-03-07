FROM ubuntu

# we're prompted if we don't add this
ENV DEBIAN_FRONTEND=noninteractive

## install the deps for running greenchecks
RUN apt-get update \
  && apt-get install git \
  php-fpm php-pear php7.2-dev php7.2-mbstring php7.2-bcmath php-mysql php7.2-curl \
  mysql-client \
  php7.2-redis \
  zip unzip \
  netcat --yes

# so we can sanity check the box
# RUN apt-get install mysql-client mysql-server redis --yes

# RUN service mysql start

# # fetch the composer image, and copy the composer binary
# # so we can run composer later in our container
COPY --from=composer /usr/bin/composer /usr/bin/composer

# set up the src
RUN mkdir -p /app

COPY packages/greencheck/composer.json /app/composer.json

WORKDIR /app/
RUN /usr/bin/composer install

COPY wait-for /app/wait-for
COPY runtests.sh /app/runtests.sh
COPY runphpstan.sh /app/runphpstan.sh
COPY packages/greencheck /app/
COPY * /app/source/

# RUN ./bin/phpunit -c phpunit.xml.dist