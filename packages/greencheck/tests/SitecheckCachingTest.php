<?php
require_once __DIR__ . '/TestConfiguration.php';
require_once __DIR__ . '/SitecheckTestCase.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
class SitecheckCachingTest extends SitecheckTestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    protected $em = null;

    protected $redis = null;


    public function testSecondCheckShouldBeCached()
    {
        $logger = new SQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger($logger);

        $date = new \DateTime('now');

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());
        $this->assertEquals(11, count($logger->getQueries()));

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
        $this->assertEquals(6, count($result));
        $this->assertEquals('www.nu.nl', $result[4]->getUrl());

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertTrue($result->isCached());

        $result = $greencheck->findBy(array());
        $this->assertEquals(7, count($result));
        $this->assertEquals('www.nu.nl', $result[5]->getUrl());

        $result = $this->sitecheck->check('www.netexpo.nl');
        $this->assertFalse($result->isCached());

        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());

        $result = $this->sitecheck->check('www.netexpo.nl');
        $this->assertTrue($result->isCached());

        $result = $greencheck->findBy(array());
        $this->assertEquals(10, count($result));
        $this->assertEquals('www.netexpo.nl', $result[7]->getUrl());
    }

    public function testResultCachingCanBeReset()
    {
        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertTrue($result->isCached());

        $this->cache->reset();

        $result = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isCached());
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
}
