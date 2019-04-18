<?php
require_once __DIR__ . '/TestConfiguration.php';

use phpmock\phpunit\PHPMock;
use Symfony\Bridge\PhpUnit\DnsMock;
use TGWF\Greencheck\Sitecheck;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use TGWF\Greencheck\Sitecheck\Aschecker;

class SitecheckTest extends TestCase
{
    use PHPMock;

    /**
     *
     * @var Sitecheck
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

        /* mock out the `getIpAddressesForUrl`function on  TGWF\Greencheck\Sitecheck\DnsFetcher now, before we call `__construct` on `Sitecheck`

        */
        $dns = $this->createMock(Sitecheck\DnsFetcher::class);
        $dns->method('getIpAddressesForUrl')->willReturn(
            // make the return value match the signature in DNSFetcher
            [
                'ip' => '94.75.237.71',
                'ipv6' => false
            ]);
        /* */

        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, new Sitecheck\Logger($entityManager), 'test', $dns);

        //Cleanup all cache entries to correctly test
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();

        /*
        $dns = $this->getFunctionMock('TGWF\Greencheck\Sitecheck', "dns_get_record");
        $dns->expects($this->once())->with(['www.greenweb.nl', DNS_A])->willReturn([
            [
                'host' => 'www.greenweb.nl',
                'class' => 'IN',
                'ttl' => 617,
                'type' => 'A',
                'mocked' => true,
                'ip' => '94.75.237.71', // Groene hosting fixture
            ]
        ]);
        */
    }

    /**
     * When checking a valid url, a sitecheck result object should be returned
     * @return unknown_type
     */
    public function testCheckValidUrlShouldReturnSitecheckResultObject()
    {
        $result = $this->sitecheck->check('www.iping.nl');
        $this->assertTrue($result instanceof TGWF\Greencheck\SitecheckResult, 'Result is not an SitecheckResult');
    }

    /**
     * When checking a valid url, a sitecheck result object should be returned
     * @return unknown_type
     */
    public function testCheckValidUrlShouldReturnResultObjectWithCheckedDate()
    {
        $date = new \DateTime('now');
        $result = $this->sitecheck->check('www.iping.nl');

        $this->assertTrue($result->getCheckedAt() >= $date);
    }
    
    
    /**
    * Before we can check an url, we need to validate the url to check if it's a correct url
    *
    * @return void
    */
    public function testCheckingAValidFullUrlShouldReturnTrue()
    {
        /*
        trying to define a mock here doesn't affect the return value either, so presumably
        it's not a case of the object already been instantiated before we can mock
        the function

        $dns = $this->getFunctionMock('TGWF\Greencheck\Sitecheck\DnsFetcher', "getIpAddressesForUrl");
        $dns->expects($this->once())->with('http://www.iping.nl/en/test')->willReturn(
            // make the return value match the signature in DNSFetcher, and match a green url
            [
                'ip' => '94.75.237.71',
                'ipv6' => false
            ]);
        */

        // we get `188.93.150.80` as our value still

        $result = $this->sitecheck->check('http://www.iping.nl/en/test');

        $this->assertnull($result);
        // $this->assertTrue($result->isGreen());
    }

    /**
     * The returned sitecheck result object should contain the checked url
     * @return unknown_type
     */
    public function testSitecheckResultObjectShouldContainCheckedUrl()
    {
        $result = $this->sitecheck->check('www.iping.nl');

        $this->assertEquals('www.iping.nl', $result->getCheckedUrl());
        $this->assertEquals('188.93.150.80', $result->getIpAddress('ipv4'));
    }

    /**
     * The returned sitecheck result object should contain the checked ip from the url
     * @return unknown_type
     */
    public function testSitecheckResultObjectShouldContainCheckedIp()
    {
        $result = $this->sitecheck->check('www.iping.nl');
        
        $this->assertEquals('188.93.150.80', $result->getIpAddress('ipv4'));
    }

    /**
     * A valid url should return a valid ip adress
     * @return unknown_type
     */
    public function testValidUrlShouldReturnValidIpAdress()
    {
        $ip = $this->sitecheck->getIpForUrl('www.iping.nl');
        $this->assertEquals('188.93.150.80', $ip['ipv4']);
    }

    /**
     * An invalid url should return false when getting the ipadress for it
     * @return unknown_type
     */
    public function testInvalidUrlShouldReturnFalseForIP()
    {
        $ip = $this->sitecheck->getIpForUrl('a.b.c');
        $this->assertEquals(false, $ip['ipv4']);
    }

    /**
     * If the url is not green, false should be returned
     * @return unknown_type
     */
    public function testCheckNotGreenUrlShouldReturnFalse()
    {
        $result = $this->sitecheck->check('www.free.fr');

        $this->assertFalse($result->isGreen());
        $this->assertTrue($result->isData());
    }
    
    public function testCountryTldsShouldHaveMoreThan5checksBeforeTheyHaveData()
    {
        $result = $this->sitecheck->getCountryTlds();
        $this->assertEquals(3, count($result));
    }

    /**
     * If the url is found in the ip search, then the search result should be returned
     * @return unknown_type
     */
    public function testUrlInIpSearchShouldReturnSearchResult()
    {
        $result = $this->sitecheck->checkIp('www.xs4all.nl');
        $this->markTestIncomplete(
            'We do not seem to have a checkIp function on the greencheckIP class, which is what the the sitecheck calls in $this->sitecheck->checkIp'
        );
        //
            // greencheck anymore
        $this->assertEquals('194.109.21.4', $result->getIpStart());
        $this->assertEquals('194.109.21.4', $result->getIpEind());
        $this->assertEquals('Greencheck dummy provider', $result->getHostingprovider()->getNaam());
        $this->assertEquals(true, $result->isActive());
    }

    /**
     * If the url is found in the ip search, then the search result should be returned
     * @return unknown_type
     */
    public function testValidIPRangeShouldReturnTrue()
    {
        $builder = new ValidatorBuilder();
        $builder->enableAnnotationMapping();
        $validator = $builder->getValidator();

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('94.75.237.89');
        $greencheckIp->setIpEind('94.75.237.89');
        $this->assertTrue($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) == 0);
        
        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('94.75.237.89');
        $greencheckIp->setIpEind('94.75.237.100');
        $this->assertTrue($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) == 0);

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('94.75.237.89');
        $greencheckIp->setIpEind('94.75.237.88');
        $this->assertFalse($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) > 0);
    }

    /**
     * If the url is found in the ip search, then the search result should be returned
     * @return unknown_type
     */
    public function testGreencheckIPShouldOnlyAllowPublicIpRanges()
    {
        $builder = new ValidatorBuilder();
        $builder->enableAnnotationMapping();
        $validator = $builder->getValidator();

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('94.75.237.89');
        $greencheckIp->setIpEind('94.75.237.89');
        $this->assertTrue($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) == 0);
        
        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('10.0.0.1');
        $greencheckIp->setIpEind('10.0.5.21');
        $this->assertTrue($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) > 0);

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('10.0.0.1');
        $greencheckIp->setIpEind('11.0.5.21');
        $this->assertTrue($greencheckIp->isValidIpRange());
        $errors = $validator->validate($greencheckIp);
        $this->assertTrue(count($errors) > 0);
    }

    /**
     * No ip search result, then return null
     * @return unknown_type
     */
    public function testUrlNotInIpSearchShouldReturnNull()
    {
        $result = $this->sitecheck->checkIp('www.nu.nl');
        $this->assertNull($result);
    }

    /**
     * A website that is hosted by a green hosting provider should be qualified as a green website
     * @return unknown_type
     */
    public function testWebsiteHostedByGreenHostingProviderShouldReturnWebsiteIsGreen()
    {
        $result    = $this->sitecheck->check('www.xs4all.nl');
        $this->assertTrue($result->isGreen());
    }

    /**
     * A green hosting provider, that hosts a website, has an entry in our database, and so
     * isHostingProvider() will return true.
     *
     * @return void
     */
    public function testWebsiteHostedByHostingProviderShouldReturnTrueForIsHostingProvider()
    {
        $result    = $this->sitecheck->check('www.xs4all.nl');
        $this->assertTrue($result->isHostingProvider());
    }

    /**
     * The result for a check for a website hosted by a green hosting provider should store the
     * id of this hosting provider for later retrieval
     * @return unknown_type
     */
    public function testWebsiteHostedByGreenHostingProviderShouldReturnIdOfHostingProvider()
    {
        /*
        DnsMock::register(TGWF\Greencheck\Sitecheck\DnsFetcher::class);
        DnsMock::register(Aschecker::class);
        DnsMock::withMockedHosts([
            'www.greenweb.nl' => ,
            Aschecker::ipv4ToReverseDnsAdressNotation('94.75.237.71'). '.origin.asn.cymru.com' => [
                [
                    'host' => "94.75.237.71.origin.asn.cymru.com",
                    'class' => "IN",
                    'ttl' => 7111,
                    'type' => "TXT",
                    'txt' => "49750 | 141.138.168.0/21 | NL | ripencc | 2011-07-01",
                    'entries' => [
                        0 => "49750 | 141.138.168.0/21 | NL | ripencc | 2011-07-01"
                    ]
                ]
            ]
        ]);
        */

        $result    = $this->sitecheck->check('www.greenweb.nl');
        $this->assertNotNull($result);

        $this->markTestIncomplete(
            'We do not have a site with a green provider in the fixtures to check against. Do we need to fake a dns resolution to a GreenIP for a hoster here?'
        );
        $this->assertEquals('Groene Hosting', $result->getHostingProvider()->getNaam());
    }

    /**
     * Same test as above, another url with another hosting provider
     * @return unknown_type
     */
    public function testWebsiteHostedByAnotherGreenHostingProviderShouldReturnIdOfHostingProvider()
    {
        $result    = $this->sitecheck->check('www.stuurterug.nl');
        $this->markTestIncomplete(
            'result returns as null here too. ping can not find it either now. dead domain?'
        );
        $this->assertEquals('Xs4all', $result->getHostingProvider()->getNaam());
    }

    /**
     * A grey hosting provider that is hosting the website will not have an entry in our database.
     * Because of that, the isHostingProvider() call should return false
     *
     * @return void
     */
    public function testWebsiteHostedByGreyHostingProviderShouldReturnFalseForIsHostingProvider()
    {
        $result    = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isHostingProvider());
    }

    /**
     * A website hosted with a grey hosting provider should return the result that the website is not green
     * @return unknown_type
     */
    public function testWebsiteHostedByGreyHostingProviderShouldReturnWebsiteIsNotGreen()
    {
        $result    = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isGreen());
    }

    /**
     * If the url is found in the customer search, then the search result should be returned
     * @return unknown_type
     */
    public function testUrlInCustomerSearchShouldReturnSearchResult()
    {
        $gc = $this->em->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $result = $gc->checkUrl('www.geluidsnet.nl');

        $this->assertEquals('www.geluidsnet.nl', $result->getUrl());
        $this->assertEquals(new \DateTime('2009-01-01'), $result->getDatumBegin());
        $this->assertEquals(new \DateTime('2050-01-01'), $result->getDatumEind());
    }

    /**
     * A website that is a customer should return green, unless the date has passed.
     * @return unknown_type
     */
    public function testWebsiteThatCompensatesShouldReturnIsGreen()
    {
        $result    = $this->sitecheck->check('www.geluidsnet.nl');
        $this->assertTrue($result->isGreen());
    }

    /**
     * A website that is a customer should return green, unless the date has passed.
     * @return unknown_type
     */
    public function testWebsiteThatNoLongerCompensatesShouldReturnIsNotGreen()
    {
        $result    = $this->sitecheck->check('www.webber.nl');
        $this->assertFalse($result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     * https://bitbucket.org/cleanbits/greencheck/issue/8/support-checking-wwwdomaintld-and
     */
    public function testWwwShouldReturnResultForNonWWW()
    {
        $result_www = $this->sitecheck->check('www.marcgijzen.nl'); // Not Registered
        $result     = $this->sitecheck->check('marcgijzen.nl');    // Registered

        $this->assertEquals('www.marcgijzen.nl', $result_www->getCheckedUrl());
        $this->assertEquals('marcgijzen.nl', $result->getCheckedUrl());

        $this->assertEquals(true, $result_www->isGreen());
        $this->assertEquals(true, $result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     * https://bitbucket.org/cleanbits/greencheck/issue/8/support-checking-wwwdomaintld-and
     */
    public function testNonWwwShouldReturnResultForWWW()
    {
        $result_www = $this->sitecheck->check('www.arendjantetteroo.nl'); // Registered
        $result     = $this->sitecheck->check('arendjantetteroo.nl');    // Not Registered

        $this->assertEquals('www.arendjantetteroo.nl', $result_www->getCheckedUrl());
        $this->assertEquals('arendjantetteroo.nl', $result->getCheckedUrl());

        $this->assertEquals(true, $result_www->isGreen());
        $this->assertEquals(true, $result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     * https://bitbucket.org/cleanbits/greencheck/issue/8/support-checking-wwwdomaintld-and
     */
    public function testAnotherSubDomainShouldNotReturnResultForWWW()
    {
        $result_www = $this->sitecheck->check('www.arendjantetteroo.nl'); // Registered
        $result     = $this->sitecheck->check('blog.arendjantetteroo.nl');    // Not Registered

        $this->assertEquals('www.arendjantetteroo.nl', $result_www->getCheckedUrl());
        $this->assertEquals('blog.arendjantetteroo.nl', $result->getCheckedUrl());

        $this->assertEquals(true, $result_www->isGreen());
        $this->assertEquals(false, $result->isGreen());
    }

    /**
     * Make sure the smallest ip range is returned when matched on ip
     * In order to have small hosting resellers get back instead of the big ones
     * https://bitbucket.org/cleanbits/greencheck/issue/4/return-smallest-ip-range-while-checking
     */
    public function testReturnTheSmallestIpRangePossible()
    {
        $result = $this->sitecheck->checkIp('www.ipingtest.com');
        $this->markTestIncomplete(
            'This is calls checkIp on sitecheck, which calls a getIpForUrl, which is returning a null result'
        );
        // are we getting anything back at all?
        $this->assertNotNull($result);
        $this->assertEquals('94.75.237.89', $result->getIpStart());
        $this->assertEquals('94.75.237.89', $result->getIpEind());
        $this->assertEquals('Xs4all', $result->getHostingprovider()->getNaam());
        $this->assertEquals(true, $result->isActive());
    }

    /**
     * Make sure adding ip ranges work correctly with signed/unsigned ints
     * https://bitbucket.org/cleanbits/greencheck/issue/7/fix-ip2long-on-32bit-64-returns-signed-int
     */
    public function testAddIpRangeToDatabase()
    {
        $greencheck = $this->em->getRepository("TGWF\Greencheck\Entity\GreencheckIp");

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('94.75.237.1');
        $greencheckIp->setIpEind('94.75.237.1');
        $greencheckIp->setActive(true);
        $this->em->persist($greencheckIp);
        $this->em->flush();

        $this->assertEquals(1582034177, $greencheckIp->getIpStartLong());
        $this->assertEquals(1582034177, $greencheckIp->getIpEindLong());

        $result = $greencheck->find($greencheckIp->getId());

        $this->assertEquals(1582034177, $result->getIpStartLong());
        $this->assertEquals(1582034177, $result->getIpEindLong());
        $this->assertEquals('94.75.237.1', $result->getIpStart());
        $this->assertEquals('94.75.237.1', $result->getIpEind());

        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();
        $greencheckIp->setIpStart('213.108.104.0');
        $greencheckIp->setIpEind('213.108.104.1');
        $greencheckIp->setActive(true);
        $this->em->persist($greencheckIp);
        $this->em->flush();

        $this->assertEquals(3580651520, $greencheckIp->getIpStartLong());
        $this->assertEquals(3580651521, $greencheckIp->getIpEindLong());

        $result = $greencheck->find($greencheckIp->getId());

        $this->assertEquals(3580651520, $result->getIpStartLong());
        $this->assertEquals(3580651521, $result->getIpEindLong());
        $this->assertEquals('213.108.104.0', $result->getIpStart());
        $this->assertEquals('213.108.104.1', $result->getIpEind());
    }

    /**
     * Checking an ip on a green server should return green
     * https://bitbucket.org/cleanbits/greencheck/issue/15/support-lookup-of-ip-adresses
     * @return unknown_type
     */
    public function testIpAsUrlShouldReturnResult()
    {
        $result = $this->sitecheck->check('94.75.237.89');
        $this->assertEquals(true, $result->isGreen());
    }
    
    /**
     * Checking an ip on a green server should return green
     * https://bitbucket.org/cleanbits/greencheck/issue/15/support-lookup-of-ip-adresses
     * @return unknown_type
     */
    public function testInvalidIpAsUrlShouldReturnNoResult()
    {
        $result = $this->sitecheck->check('94.75.237.8912');
        $this->assertEquals(false, $result);
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testGetIpv6ForurlForIpv6()
    {
        $result = $this->sitecheck->getHostByName('www.ipv6.xs4all.nl');
        $this->markTestIncomplete(
            'This website has moved hosts, so we need a different fixture'
        );

        $this->assertFalse($result['ip']);
        // this is returning false too at present. Are IPv5 looks
        // returning anything?
        $this->assertEquals('2001:888::18:0:0:0:80', $result['ipv6']);
    }

    /**
     * Checking an ipv6 on a green server should return green
     * https://bitbucket.org/cleanbits/greencheck/issue/15/support-lookup-of-ip-adresses
     * @return unknown_type
     */
    public function testIpv6AsUrlShouldReturnResult()
    {
        $result = $this->sitecheck->check('2001:888::18:0:0:0:80');
        $this->assertEquals('2001:888::18:0:0:0:80', $result->getIpAddress('ipv6'));
        $this->assertEquals(true, $result->isGreen());
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testUrlWithIpv6ShouldReturnValidResult()
    {
        $result = $this->sitecheck->check('www.ipv6.xs4all.nl');
        $this->markTestIncomplete(
            'This website has moved hosts, so we need a different fixture'
        );

        $this->assertEquals('2001:888::18:0:0:0:80', $result->getIpAddress('ipv6'));
        $this->assertTrue($result->isGreen());
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testResultForUrlWithIpv6IpAdressShouldReturnGreen()
    {
        $result = $this->sitecheck->getHostByName('webmail.mailplatform.eu');
        $this->markTestIncomplete(
            'This website has moved hosts and no longer green. We need a different fixture'
        );

        $this->assertEquals('2001:4b98:dc0:41:216:3eff:fedd:3317', $result['ipv6']);
        $this->assertEquals('92.243.6.32', $result['ip']);

        $result = $this->sitecheck->check('webmail.mailplatform.eu');
        // they are no longer green now
        $this->assertFalse($result->isGreen());
        // $this->assertTrue($result->isGreen());
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testResultForIpv6NotInRangeShouldReturnFalse()
    {
        $greencheckIp = new \TGWF\Greencheck\Entity\GreencheckIp();

        $result = $this->sitecheck->check('2001:4b98:dc0:41:216:3eff:fecb:4b0e');
        $this->assertFalse($result->isGreen());
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testResultForipv6InIpv6RangeShouldReturnGreen()
    {
        /**
         * Range is 2a00:1950:100::/48 -
         * Stored as 2a00:1950:0100:0000:0000:0000:0000:0000 - 2a00:1950:0100:ffff:ffff:ffff:ffff:ffff
         */
        $result = $this->sitecheck->check('2a00:1950:0100:0000:0000:0000:0000:0000');
        $this->assertTrue($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0100:0000:0000:0000:0000:0001');
        $this->assertTrue($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0100:ffff:ffff:cccc:ffff:fffe');
        $this->assertTrue($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0100:ffff:ffff:ffff:ffff:fffe');
        $this->assertTrue($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0100:ffff:ffff:ffff:ffff:ffff');
        $this->assertTrue($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0101:ffff:ffff:ffff:ffff:ffff');
        $this->assertFalse($result->isGreen());

        $result = $this->sitecheck->check('2a00:1950:0099:ffff:ffff:ffff:ffff:ffff');
        $this->assertFalse($result->isGreen());
    }
}
