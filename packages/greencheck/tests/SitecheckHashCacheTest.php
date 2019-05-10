<?php
require_once __DIR__ . '/TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class SitecheckHashCachingTest extends TestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    protected $redis = null;

    protected $em = null;

    public function setUp(): void
    {
        // reset database to known state
        TestConfiguration::setupDatabase();

        $config     = TestConfiguration::$config;
        $entityManager   = TestConfiguration::$em;
        $this->em = $entityManager;

        // Setup the cache
        $cache = new Sitecheck\Cache($config);
        $cache->setCache('default');
        $redisCache = $cache->getCache();


        $logger = new Sitecheck\Logger($entityManager, $redisCache);

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");



        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, $logger, 'test');

        //Cleanup all cache entries to correctly test
        // $cache = $this->sitecheck->getCache();
        // $cache->deleteAll();

    }

    public function testRunningCheckAddsToDomainCache()
    {
        $cache = $this->sitecheck->getCache();
        // $cache = $this->sitecheck->getCacheObject();
        // $redisCache = $cache->getCache();

        // $logger = new SQLLogger();
        // $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $date = new \DateTime('now');
        $formattedDate = $date->format("Y-m-d");

        $result = $this->sitecheck->check('www.nu.nl');

        $cachedUrlData = $cache->fetch('domains:www.nu.nl');
        $this->assertEquals("www.nu.nl", $cachedUrlData->url);
        $this->assertEquals(false, $cachedUrlData->green);
        $this->assertStringContainsString($formattedDate, $cachedUrlData->date);

        // this might be better in a teardown method, but because we go through
        // the PredisCache, their interface doesn't support a `deleteAll()` method
        // or similar
        $cachedUrlData = $cache->delete('domains:www.nu.nl');
    }

}
