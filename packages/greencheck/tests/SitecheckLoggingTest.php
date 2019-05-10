<?php
require_once __DIR__ . '/TestConfiguration.php';
require_once __DIR__ . '/SitecheckTestCase.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class SitecheckLoggingTest extends SitecheckTestCase
{
    /**
     *
     * @var Greencheck_Sitecheck
     */
    protected $sitecheck = null;

    protected $em = null;

    protected $redis = null;

    public function testGreencheckTableShouldContainCheckedUrls()
    {
        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));
    }

    public function testCheckShouldBeLoggedByDefault()
    {
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(6, count($result));
    }

    public function testLoggingShouldContainDate()
    {
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertGreaterThan('2009-01-01', $result->getDatum());
    }

    public function testLoggingCheckShouldContainCorrectResults()
    {
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('www.nu.nl', $result->getUrl());
    }

    public function testDisableLoggingShouldBePossible()
    {
        $this->sitecheck->disableLog();
        $result    = $this->sitecheck->check('www.nu.nl');

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
