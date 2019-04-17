<?php
//doctrine-cli.php
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/bootstrap.php';
 
$helperSet = new HelperSet(array(
    'em' => new EntityManagerHelper($em),
    'conn' => new ConnectionHelper($em->getConnection())
));
ConsoleRunner::run($helperSet);
