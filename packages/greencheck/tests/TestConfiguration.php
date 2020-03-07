<?php

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use TGWF\Greencheck\Fixtures\LoadIpData;
use TGWF\Greencheck\Fixtures\LoadUrlData;
use TGWF\Greencheck\Fixtures\LoadAsData;
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

    public static function getIpUrlMapping()
    {
        return [
            ['a.b.c',  ['ip' => false, 'ipv6' => false]],
            ['www.nonexistingurlblablalba.nl',  ['ip' => false, 'ipv6' => false]],

            ['www.iping.nl', ['ip' => '94.75.237.71','ipv6' => false]],
            ['www.cleanbits.nl', ['ip' => '94.75.237.71','ipv6' => false]],


            // use for testing subdomain handling
            ['no-www-registered.nl', ['ip' => '94.75.237.71','ipv6' => false]],
            ['www.no-www-registered.nl', ['ip' => '94.75.237.71','ipv6' => false]],
            ['blog.no-www-registered.nl', ['ip' => '194.75.237.69','ipv6' => false]],

            // outside a fixture range
            ['www.free.fr', ['ip' => '94.75.237.69','ipv6' => false]],
            // xs4all ip range
            ['www.xs4all.nl', ['ip' => '194.109.21.4','ipv6' => false]],

            ['www.ipv6.xs4all.nl', ['ip' => false,'ipv6' => '2001:888::18:0:0:0:80']],
            ['also.xs4all.hosted.nl', ['ip' => '194.109.21.4','ipv6' => false]],

            ['was.greenbutexpired.nl', ['ip' => '194.75.237.71','ipv6' => false]],
            // outside ip range for checkIp function
            ['www.nu.nl', ['ip' => '94.75.237.69','ipv6' => false]],
            // not a real ip address
            ['94.75.237.8912', ['ip' => '94.75.237.8912','ipv6' => false]],
            // Not green Ips but green AS
            ['www.netexpo.nl', ['ip' => '88.151.33.85','ipv6' => false]],
            ['www.ashoster.nl', ['ip' => '88.151.33.85','ipv6' => false]],
            // looking up a url with an ipv6 address
            ['webmail.mailplatform.eu', ['ip' => '92.243.6.32', 'ipv6' => '2001:4b98:dc0:41:216:3eff:fedd:3317']],
            // not an address
            ['null', ['ip' => false, 'ipv6' => false]],
            [null, ['ip' => false, 'ipv6' => false]]
        ];
    }
}
