# Run package tests
composer install

# clear the test db
#php ./tests/doctrine-cli.php orm:schema-tool:drop --force

# create the test db
#php ./tests/doctrine-cli.php orm:schema-tool:create

# run the api tests
./vendor/bin/simple-phpunit -c phpunit.xml.dist

