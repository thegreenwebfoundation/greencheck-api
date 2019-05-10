<?php
require_once __DIR__ . '/TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
class SitecheckCachingTest extends TestCase
{
    /**
     *
     * @var Greencheck_Sitecheck
     */
    protected $sitecheck = null;

    protected $em = null;

    protected $redis = null;

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
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();
    }

    public function testSecondCheckShouldBeCached()
    {
        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $date = new \DateTime('now');

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());
        $this->assertEquals(8, count($logger->getQueries()));

        sleep(1);

        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);
        
        $newdate = new \DateTime('now');
        $result = $this->sitecheck->check('www.nu.nl');

        $this->assertTrue($result->isCached());
        $this->assertTrue($result->getCheckedAt() > $date);
        $this->assertTrue($result->getCheckedAt() >= $newdate);
        
        /*
         * Only do queries to store the result
         *
         * Start transaction, greencheck table, commit
         */
        $this->assertEquals(3, count($logger->getQueries()));
    }

    /**
     * Test for #11 : Log every request even if cached
     */
    public function testSecondCheckWhenCachedShouldBeLogged()
    {
        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());

        $result = $greencheck->findBy(array());
        $this->assertEquals(5, count($result));
        $this->assertEquals('www.nu.nl', $result[4]->getUrl());

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertTrue($result->isCached());

        $result = $greencheck->findBy(array());
        $this->assertEquals(6, count($result));
        $this->assertEquals('www.nu.nl', $result[5]->getUrl());

        $result = $this->sitecheck->check('www.netexpo.nl');
        $this->assertFalse($result->isCached());

        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());

        $result = $this->sitecheck->check('www.netexpo.nl');
        $this->assertTrue($result->isCached());

        $result = $greencheck->findBy(array());
        $this->assertEquals(8, count($result));
        $this->assertEquals('www.netexpo.nl', $result[7]->getUrl());
    }

    public function testResultCachingHasLifetimeOf1Hour()
    {
        $cache = $this->sitecheck->getCacheObject();

        $this->assertEquals(3600, $cache->getTtl('result'));
    }

    public function testResultCachingCanBeReset()
    {
        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertTrue($result->isCached());

        $this->sitecheck->resetCache('result');

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());
    }

    public function testHostnameLookupsCachingHasLifetimeOf1Day()
    {
        $cache = $this->sitecheck->getCacheObject();

        $this->assertEquals(3600*24, $cache->getTtl('hostbynamelookups'));
    }

    public function testHostnameLookupsShouldComeFromCacheSecondTime()
    {
        $result = $this->sitecheck->getHostByName('www.nu.nl');
        $this->assertFalse($result['cached']);

        $result = $this->sitecheck->getHostByName('www.nu.nl');
        $this->assertTrue($result['cached']);
    }

    public function testAsLookupsShouldComeFromCacheSecondTime()
    {
        $result = $this->sitecheck->getAsForUrl('www.nu.nl');
        $this->assertFalse($result['cached']);

        $result = $this->sitecheck->getAsForUrl('www.nu.nl');
        $this->assertTrue($result['cached']);
    }

    public function testAsChecksInDatabaseShouldComeFromCacheSecondTime()
    {
        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $result = $this->sitecheck->checkAs('www.netexpo.nl');
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
        $this->assertEquals('49750', $result->getAsn());
        // select from greencheck, select from hosting providers
        $this->assertEquals(2, count($logger->getQueries()));

        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $result = $this->sitecheck->checkAs('www.netexpo.nl');
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
        $this->assertEquals('49750', $result->getAsn());

        $this->assertEquals(0, count($logger->getQueries()));
    }

    public function testIpChecksInDatabaseShouldComeFromCacheSecondTime()
    {
        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $result = $this->sitecheck->checkip('www.netexpo.nl');
        $this->assertEquals(1, count($logger->getQueries()));

        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $result = $this->sitecheck->checkIp('www.netexpo.nl');

        $this->assertEquals(0, count($logger->getQueries()));
    }

    public function testAsLookupsCachingHasLifetimeOf1Day()
    {
        $cache = $this->sitecheck->getCacheObject();

        $this->assertEquals(3600*24, $cache->getTtl('aslookups'));
    }

    public function testNotConfiguredCachingHasLifetimeOf2Hours()
    {
        $cache = $this->sitecheck->getCacheObject();

        $this->assertEquals(3600*2, $cache->getTtl('testlookups'));
    }

    public function testCacheCanBeSet()
    {
        $cache = new \Doctrine\Common\Cache\FilesystemCache('/tmp');
        
        $this->sitecheck->setCache('test', $cache);
        $this->assertEquals($cache, $this->sitecheck->getCache('test'));
    }

    public function testCacheCanBeDisabled()
    {
        $this->sitecheck->disableCache();
        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());
    }
}
