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
        $entityManager   = TestConfiguration::$em;
        $this->em = $entityManager;

        // Setup the cache
        $cache = new Sitecheck\Cache($config);
        $cache->setCache('default');

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, new Sitecheck\Logger($entityManager), 'test');

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
