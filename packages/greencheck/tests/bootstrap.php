<?php
/**
 * Bootstrap doctrine and configuration
 *
 */

function getConfigFilePath ()
{
    if (getenv('TGWF_CONFIG_FILE_PATH')) {
        return getenv('TGWF_CONFIG_FILE_PATH');
    }
    return '/config.yml';
}

$rootDir = __DIR__ . '/..';

$configFilePath = __DIR__ . getConfigFilePath();

if (!$loader = @include $rootDir .'/vendor/autoload.php') {
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

if (!file_exists($configFilePath)) {
    $message = <<< EOF
<p>No config.yml found - (looked at  \$configFilePath: $configFilePath)</p>
<p>Please copy the config.dist.yml to config.yml and configure the settings in this file:</p>
<pre>
    cp config.dist.yml config.yml
    vi config.yml
</pre>
EOF;

    if (PHP_SAPI === 'cli') {
        $message = strip_tags($message);
    }

    die($message);
}

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache as Cache;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\ClassLoader;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser;

$yaml = new Parser();

$yamlconfig = $yaml->parse(file_get_contents($configFilePath));

//configuration
$config = new Configuration();
$cache = new Cache();
$config->setQueryCacheImpl($cache);
$config->setProxyDir($rootDir .'/src/EntityProxy');
$config->setProxyNamespace('EntityProxy');
$config->setAutoGenerateProxyClasses(true);

// DEBUG
//$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());

//mapping (example uses annotations, could be any of XML/YAML or plain PHP)
AnnotationRegistry::registerFile($rootDir .'/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
$driver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
    new Doctrine\Common\Annotations\AnnotationReader(),
    array($rootDir .'/src/Entity')
);
$config->setMetadataDriverImpl($driver);
$config->setMetadataCacheImpl($cache);
$config->setResultCacheImpl($cache);

//getting the EntityManager
$em = EntityManager::create(
    $yamlconfig['greencheck']['db'],
    $config
);

// Fix the enum problem in mysql
$platform = $em->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');