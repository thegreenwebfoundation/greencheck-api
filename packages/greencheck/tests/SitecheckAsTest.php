<?php
require_once __DIR__ . '/TestConfiguration.php';

use phpmock\phpunit\PHPMock;
use Symfony\Bridge\PhpUnit\DnsMock;
use TGWF\Greencheck\Sitecheck;

use PHPUnit\Framework\TestCase;

class SitecheckAsTest extends TestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    public function setUp() :void
    {
        // reset database to known state
        TestConfiguration::setupDatabase();

        $config     = TestConfiguration::$config;
        $entityManager   = TestConfiguration::$em;

        // Setup the cache
        $cache = new Sitecheck\Cache($config);
        $cache->setCache('default');

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        // @todo this feels *really* messy. See if this makes sense to move
        // to TestConfiguration.php
        global $map;
        require_once __DIR__ . '/urlMap.php';


        $dns = $this->createMock(Sitecheck\DnsFetcher::class);
        $dns->method('getIpAddressesForUrl')->will($this->returnValueMap($map));

        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, new Sitecheck\Logger($entityManager), 'test', $dns);

        //Cleanup all cache entries to correctly test
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();
    }

    /**
     * Check that we can get the as information for an url
     * https://bitbucket.org/cleanbits/greencheck/issue/1/ip-to-asn-mapping-support
     */
    public function testGetAsForurl()
    {
        $result = $this->sitecheck->getAsForUrl('www.netexpo.nl');
        //"49750 | 194.242.18.0/23 | NL | ripencc | 2009-09-03"

        $this->assertEquals(array('49750'), $result['as']);

        $this->assertEquals('88.151.32.0/22', $result['iprange']);
        $this->assertEquals('NL', $result['country']);
        $this->assertEquals('ripencc', $result['rir']);
        $this->assertEquals('2006-02-08', $result['date']);
        $this->assertEquals('88.151.33.85', $result['ip']);
    }

    /**
     * Check that we get a green result for a green as
     * https://bitbucket.org/cleanbits/greencheck/issue/1/ip-to-asn-mapping-support
     */
    public function testRunAsCheckForValidAndGreenUrlShouldReturnAs()
    {
        $result = $this->sitecheck->checkAs('www.netexpo.nl');
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
        $this->assertEquals('49750', $result->getAsn());
    }

    /**
     * Check that we get a green result for a green as
     * https://bitbucket.org/cleanbits/greencheck/issue/1/ip-to-asn-mapping-support
     */
    public function testAsCheckForValidAndGreenUrlShouldReturnGreen()
    {
        $result = $this->sitecheck->check('www.netexpo.nl');

        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
    }
    
    /**
     * Check that we get a green result for a green as with double as numbers
     * https://bitbucket.org/cleanbits/greencheck/issue/13/dubbele-as-nummers-in-as-output-niet-groen
     */
    public function testDoubleAsNumbersInAsOutputShouldReturnGreen()
    {
        $result = $this->sitecheck->check('http://www.ashoster.nl');
        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals('AS Hoster', $result->getHostingProvider()->getNaam());
    }
    /**
     * Check that we can get the as information for an url based on the ipv4 network
     */
    public function testGetAsForurlForIpv4()
    {
        $result = $this->sitecheck->getAsForUrl('www.xs4all.nl');
        //"3265 | 194.109.0.0/16 | NL | ripencc | 1996-05-13"
        
        $this->assertEquals(array('3265'), $result['as']);
        $this->assertEquals('194.109.0.0/16', $result['iprange']);
        $this->assertEquals('NL', $result['country']);
        $this->assertEquals('ripencc', $result['rir']);
        $this->assertEquals('1996-05-13', $result['date']);
        $this->assertEquals('194.109.21.4', $result['ip']);
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testGetAsForurlForIpv6()
    {
        $result = $this->sitecheck->getAsForUrl('www.ipv6.xs4all.nl');
        //"3265 | 2001:888::/30 | NL | ripencc | 2009-09-03"
        //"3265 | 2001:888::/32 | NL | ripencc | 2009-09-03"

        $this->assertEquals(array('3265'), $result['as']);
        $this->assertEquals('2001:888::/32', $result['iprange']);
        $this->assertEquals('NL', $result['country']);
        $this->assertEquals('ripencc', $result['rir']);
        $this->assertEquals('2002-08-07', $result['date']);
        $this->assertEquals('2001:888::18:0:0:0:80', $result['ipv6']);
        $this->assertFalse($result['ip']);
    }
    
    /**
     * Make sure the smallest as is returend
     */
    public function testGetSmallestAsForIpv4()
    {
        $result = $this->sitecheck->getAsForUrl('159.253.0.50');
        // "61387 | 159.253.0.0/24 | NL | ripencc | 2011-09-20" <- This one is smallest, should be returned
        // "50673 | 159.253.0.0/21 | NL | ripencc | 2011-09-20"
        $this->assertEquals(array('60775'), $result['as']);
        $this->assertEquals('159.253.0.0/24', $result['iprange']);
        $this->assertEquals('NL', $result['country']);
        $this->assertEquals('ripencc', $result['rir']);
        $this->assertEquals('2011-09-20', $result['date']);
        $this->assertEquals('159.253.0.50', $result['ip']);
    }
}
