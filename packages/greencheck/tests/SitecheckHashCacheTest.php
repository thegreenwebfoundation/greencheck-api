<?php
require_once __DIR__ . '/TestConfiguration.php';
require_once __DIR__ . '/SitecheckTestCase.php';

use TGWF\Greencheck\Sitecheck;


class SitecheckHashCachingTest extends SitecheckTestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
