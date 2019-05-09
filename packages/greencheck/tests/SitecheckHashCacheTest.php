<?php
require_once __DIR__ . '/TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
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
        $redis = $redisCache->getRedis();

        $logger = new Sitecheck\Logger($entityManager, $redis);

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");



        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, $logger, 'test');

        //Cleanup all cache entries to correctly test
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();

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
        $redis = $cache->getRedis();

        $allKeys = $redis->keys("domains*");

        $cachedUrlData = $redis->get('domains:www.nu.nl');

        $jsonDecodedData = json_decode($cachedUrlData);
        $this->assertEquals("www.nu.nl", $jsonDecodedData->url);
        $this->assertEquals(false, $jsonDecodedData->green);
        $this->assertStringContainsString($formattedDate, $jsonDecodedData->date);
    }

}
