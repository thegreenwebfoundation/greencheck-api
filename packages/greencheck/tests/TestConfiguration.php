<?php

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use TGWF\Greencheck\Fixtures\LoadIpData;
use TGWF\Greencheck\Fixtures\LoadUrlData;
use TGWF\Greencheck\Fixtures\LoadAsData;
use TGWF\Greencheck\Fixtures\LoadEnergyData;
use TGWF\Greencheck\Fixtures\LoadTldData;
use TGWF\Greencheck\Fixtures\LoadGreencheckData;

TestConfiguration::setup();

class TestConfiguration
{
    public static $appRoot;

    public static $dbAdapter;

    public static $config;

    public static $em;

    public static $cache;

    public static function setup()
    {
        $appRoot = realpath(dirname(basename(__FILE__)));

        TestConfiguration::$appRoot = $appRoot;

        error_reporting(E_ALL | E_STRICT);

        if (!$loader = @include $appRoot.'/vendor/autoload.php') {
            $message = <<< EOF
                <p>You must set up the project dependencies by running the following commands:</p>
                    <pre>
                    curl -s http://getcomposer.org/installer | php
                    php composer.phar install
                    </pre>
EOF;

            if (PHP_SAPI === 'cli') {
                $message = strip_tags($message);
            }

            die($message);
        }

        require $appRoot . '/tests/bootstrap.php';
        TestConfiguration::$em = $em;
        TestConfiguration::$cache = $cache;
        TestConfiguration::$config = $yamlconfig;
    }

    public static function setupDatabase()
    {
        $loader = new Loader();
        $loader->addFixture(new LoadIpData);
        $loader->addFixture(new LoadUrlData);
        $loader->addFixture(new LoadAsData);
        $loader->addFixture(new LoadTldData);
        $loader->addFixture(new LoadGreencheckData);

        $purger = new ORMPurger();
        $executor = new ORMExecutor(TestConfiguration::$em, $purger);
        $executor->execute($loader->getFixtures());

        TestConfiguration::$cache->deleteAll();
    }
}
