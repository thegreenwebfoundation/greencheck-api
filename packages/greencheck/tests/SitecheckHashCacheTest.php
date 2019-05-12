<?php
require_once __DIR__ . '/TestConfiguration.php';
require_once __DIR__ . '/SitecheckTestCase.php';

use TGWF\Greencheck\Sitecheck;
use Predis\Client;

class SitecheckHashCachingTest extends SitecheckTestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    public $redis = null;

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

        $redis = new Client([
            "host" => TestConfiguration::$config['greencheck']['redis']['host']
        ]);

        $logger = new Sitecheck\Logger($entityManager, $redis);

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");



        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, $logger, 'test');

        $this->redis = $logger->redis;

        //Cleanup all cache entries to correctly test
        // $cache = $this->sitecheck->getCache();
        // $cache->deleteAll();

    }

    public function testRunningCheckAddsToDomainCache()
    {
        $date = new \DateTime('now');
        $formattedDate = $date->format("Y-m-d");

        $result = $this->sitecheck->check('www.nu.nl');

        $cachedUrlData = json_decode($this->redis->get('domains:www.nu.nl'));
        $this->assertEquals("www.nu.nl", $cachedUrlData->url);
        $this->assertEquals(false, $cachedUrlData->green);
        $this->assertStringContainsString($formattedDate, $cachedUrlData->date);

        // this might be better in a teardown method, but because we go through
        // the PredisCache, their interface doesn't support a `deleteAll()` method
        // or similar
        // $cachedUrlData = $cache->delete('domains:www.nu.nl');
    }

}
