<?php
require_once __DIR__ . '/TestConfiguration.php';
require_once __DIR__ . '/SitecheckTestCase.php';

class SitecheckLoggingTest extends SitecheckTestCase
{
    public function testGreencheckTableShouldContainCheckedUrls()
    {
        $this->markTestIncomplete("Move to api");
        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));
    }

    public function testCheckShouldBeLoggedByDefault()
    {
        $this->markTestIncomplete("Move to api");
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(6, count($result));
    }

    public function testLoggingShouldContainDate()
    {
        $this->markTestIncomplete("Move to api");
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertGreaterThan('2009-01-01', $result->getDatum());
    }

    public function testLoggingCheckShouldContainCorrectResults()
    {
        $this->markTestIncomplete("Move to api");
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $result = end($result);
        $this->assertEquals('www.nu.nl', $result->getUrl());
    }

    public function testDisableLoggingShouldBePossible()
    {
        $this->markTestIncomplete("Move to api");
        $this->sitecheck->disableLog();
        $result    = $this->sitecheck->check('www.nu.nl');

        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\Greencheck");
        $result = $greencheck->findBy(array());
        $this->assertEquals(4, count($result));
    }
    
    public function testLoggingCheckShouldContainHostingProviderId()
    {
        $this->markTestIncomplete("Move to api");
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
