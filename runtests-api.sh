

# clear the test db
# php ./tests/doctrine-cli.php orm:schema-tool:drop --force

# create the test db
# php ./tests/doctrine-cli.php orm:schema-tool:create

# run the test command
./bin/phpunit -c phpunit.xml.dist

