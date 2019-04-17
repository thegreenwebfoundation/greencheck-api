<?php
require_once dirname(__FILE__) . '/../TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class Models_SitecheckLoggingTest extends TestCase
{
    /**
     *
     * @var Greencheck_Sitecheck
     */
    protected $sitecheck = null;

    protected $em = null;

    public function setUp(): void
    {
        // reset database to known state
        TestConfiguration::setupDatabase();

        $config     = TestConfiguration::$config;
        $this->em   = TestConfiguration::$em;

        // Setup the cache
        $this->cache = new Sitecheck\Cache($config);
        $this->cache->setCache('default');

        $this->sitecheck = new Sitecheck($this->em, $this->cache, 'test');

        //Cleanup all cache entries to correctly test
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();
    }

    public function testGreencheckTableShouldContainCheckedUrls()
    {
        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));
    }

    public function testCheckShouldBeLoggedByDefault()
    {
        $result    = $this->sitecheck->check('www.bliin.com');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(5, count($result));
    }

    public function testLoggingShouldContainDate()
    {
        $result    = $this->sitecheck->check('www.bliin.com');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertGreaterThan('2009-01-01', $result->getDatum());
    }
   
    public function testLoggingSourceCanBeOverWrittenInCheck()
    {
        $result    = $this->sitecheck->check('www.bliin.com', '127.0.0.1', 'testbrowser', 'bots');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\GreencheckBy");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('bots', $result->getCheckedThrough());
    }

    public function testLoggingShouldContainBrowser()
    {
        $result    = $this->sitecheck->check('www.bliin.com', '127.0.0.1', 'testbrowser');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\GreencheckBy");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('test', $result->getCheckedThrough());
        $this->assertEquals('testbrowser', $result->getCheckedBrowser());
    }

    public function testLoggingShouldContainCheckedByAsHash()
    {
        $result    = $this->sitecheck->check('www.bliin.com', '127.0.0.1');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\GreencheckBy");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('2095c30cebbeb00c42538bc7fc0c1db203dec8db', $result->getCheckedBy());
    }

    public function testLoggingCheckShouldContainCorrectResults()
    {
        $result    = $this->sitecheck->check('www.bliin.com');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('www.bliin.com', $result->getUrl());
    }

    public function testDisableLoggingShouldBePossible()
    {
        $this->sitecheck->disableLog();
        $result    = $this->sitecheck->check('www.bliin.com');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));
    }
    
    public function testLoggingCheckShouldContainHostingProviderId()
    {
        $result = $this->sitecheck->check('www.netexpo.nl');

        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
        $idhp = $result->getHostingProvider()->getId();

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);

        $this->assertEquals($idhp, $result->getIdHp());
    }
}
