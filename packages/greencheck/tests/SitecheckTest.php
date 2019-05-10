<?php
require_once __DIR__ . '/TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class SitecheckTest extends TestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck;

    protected $em;

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

        $dns = $this->createMock(Sitecheck\DnsFetcher::class);
        $dns->method('getIpAddressesForUrl')->will($this->returnValueMap(TestConfiguration::getIpUrlMapping()));

        $this->sitecheck = new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, $logger, 'test', $dns);

        //Cleanup all cache entries to correctly test
        $cache = $this->sitecheck->getCache();
        $cache->deleteAll();
    }

    /**
     * When checking a valid url, a sitecheck result object should be returned
     */
    public function testCheckValidUrlShouldReturnSitecheckResultObject()
    {
        $result = $this->sitecheck->check('www.iping.nl');
        $this->assertTrue($result instanceof TGWF\Greencheck\SitecheckResult, 'Result is not an SitecheckResult');
    }

    /**
     * When checking a valid url, a sitecheck result object should be returned
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
        // iping is mocked to return an ip out of the ip fixtures
        $result = $this->sitecheck->check('http://www.iping.nl/en/test');
        $this->assertTrue($result->isGreen());
    }

    /**
     * The returned sitecheck result object should contain the checked url
     */
    public function testSitecheckResultObjectShouldContainCheckedUrl()
    {
        $result = $this->sitecheck->check('www.iping.nl');

        $this->assertEquals('www.iping.nl', $result->getCheckedUrl());
        $this->assertEquals('94.75.237.71', $result->getIpAddress('ipv4'));
    }

    /**
     * The returned sitecheck result object should contain the checked ip from the url
     */
    public function testSitecheckResultObjectShouldContainCheckedIp()
    {
        $result = $this->sitecheck->check('www.iping.nl');
        
        $this->assertEquals('94.75.237.71', $result->getIpAddress('ipv4'));
    }

    /**
     * A valid url should return a valid ip adress
     */
    public function testValidUrlShouldReturnValidIpAdress()
    {
        $ip = $this->sitecheck->getIpForUrl('www.iping.nl');
        $this->assertEquals('94.75.237.71', $ip['ipv4']);
    }

    /**
     * An invalid url should return false when getting the ipadress for it
     */
    public function testInvalidUrlShouldReturnFalseForIP()
    {
        $ip = $this->sitecheck->getIpForUrl('a.b.c');
        $this->assertEquals(false, $ip['ipv4']);
    }

    /**
     * If the url is not green, false should be returned
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
     */
    public function testUrlInIpSearchShouldReturnSearchResult()
    {
        $result = $this->sitecheck->checkIp('www.xs4all.nl');

        $this->assertEquals('194.109.21.4', $result->getIpStart());
        $this->assertEquals('194.109.21.4', $result->getIpEind());
        $this->assertEquals('Xs4all', $result->getHostingprovider()->getNaam());
        $this->assertEquals(true, $result->isActive());
    }

    /**
     * If the url is found in the ip search, then the search result should be returned
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
     */
    public function testUrlNotInIpSearchShouldReturnNull()
    {
        $result = $this->sitecheck->checkIp('www.nu.nl');
        $this->assertNull($result);
    }

    /**
     * A website that is hosted by a green hosting provider should be qualified as a green website
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
     */
    public function testWebsiteHostedByGreenHostingProviderShouldReturnIdOfHostingProvider()
    {
        $result    = $this->sitecheck->check('www.iping.nl');
        $this->assertNotNull($result);
        $this->assertEquals('Groene Hosting', $result->getHostingProvider()->getNaam());
    }

    /**
     * Same test as above, another url with another hosting provider
     */
    public function testWebsiteHostedByAnotherGreenHostingProviderShouldReturnIdOfHostingProvider()
    {
        $result    = $this->sitecheck->check('also.xs4all.hosted.nl');
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
     */
    public function testWebsiteHostedByGreyHostingProviderShouldReturnWebsiteIsNotGreen()
    {
        $result    = $this->sitecheck->check('www.nu.nl');
        $this->assertFalse($result->isGreen());
    }

    /**
     * If the url is found in the customer search, then the search result should be returned
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
     */
    public function testWebsiteThatCompensatesShouldReturnIsGreen()
    {
        $result    = $this->sitecheck->check('www.iping.nl');
        $this->assertTrue($result->isGreen());
    }

    /**
     * A website that is a customer should return green, unless the date has passed.
     */
    public function testWebsiteThatNoLongerCompensatesShouldReturnIsNotGreen()
    {
        $result    = $this->sitecheck->check('was.greenbutexpired.nl');
        $this->assertFalse($result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     */
    public function testWwwShouldReturnResultForNonWWW()
    {
        $result_www = $this->sitecheck->check('www.no-www-registered.nl'); // Not Registered
        $result     = $this->sitecheck->check('no-www-registered.nl');    // Registered

        $this->assertEquals('www.no-www-registered.nl', $result_www->getCheckedUrl());
        $this->assertEquals('no-www-registered.nl', $result->getCheckedUrl());

        $this->assertEquals(true, $result_www->isGreen());
        $this->assertEquals(true, $result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     */
    public function testNonWwwShouldReturnResultForWWW()
    {
        $result_www = $this->sitecheck->check('www.no-www-registered.nl'); // Registered
        $result     = $this->sitecheck->check('no-www-registered.nl');    // Not Registered

        $this->assertEquals('www.no-www-registered.nl', $result_www->getCheckedUrl());
        $this->assertEquals('no-www-registered.nl', $result->getCheckedUrl());

        $this->assertEquals(true, $result_www->isGreen());
        $this->assertEquals(true, $result->isGreen());
    }

    /**
     * Make sure a registered url without www. is also found
     */
    public function testAnotherSubDomainShouldNotReturnResultForWWW()
    {
        $result = $this->sitecheck->check('no-www-registered.nl'); // Registered
        $result_blog     = $this->sitecheck->check('blog.no-www-registered.nl');    // Not Registered

        $this->assertEquals('no-www-registered.nl', $result->getCheckedUrl());
        $this->assertEquals('blog.no-www-registered.nl', $result_blog->getCheckedUrl());

        $this->assertEquals(true, $result->isGreen());
        $this->assertEquals(false, $result_blog->isGreen());
    }

    /**
     * Make sure the smallest ip range is returned when matched on ip
     * In order to have small hosting resellers get back instead of the big ones
     */
    public function testReturnTheSmallestIpRangePossible()
    {
        $result = $this->sitecheck->checkIp('also.xs4all.hosted.nl');
        $this->assertEquals('194.109.21.4', $result->getIpStart());
        $this->assertEquals('194.109.21.4', $result->getIpEind());
        $this->assertEquals('Xs4all', $result->getHostingprovider()->getNaam());
        $this->assertEquals(true, $result->isActive());
    }

    /**
     * Make sure adding ip ranges work correctly with signed/unsigned ints
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
     */
    public function testIpAsUrlShouldReturnResult()
    {
        $result = $this->sitecheck->check('94.75.237.89');
        $this->assertEquals(true, $result->isGreen());
    }
    
    /**
     * Checking an ip on a green server should return green
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
        $this->assertFalse($result['ip']);
        // this is returning false too at present. Are IPv5 looks
        // returning anything?
        $this->assertEquals('2001:888::18:0:0:0:80', $result['ipv6']);
    }

    /**
     * Checking an ipv6 on a green server should return green
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
        $this->assertEquals('2001:888::18:0:0:0:80', $result->getIpAddress('ipv6'));
        $this->assertTrue($result->isGreen());
    }

    /**
     * Check that we can get the as information for an url based on the ipv6 network
     */
    public function testResultForUrlWithIpv6IpAdressShouldReturnGreen()
    {
        $result = $this->sitecheck->getHostByName('webmail.mailplatform.eu');

        $this->assertEquals('2001:4b98:dc0:41:216:3eff:fedd:3317', $result['ipv6']);
        $this->assertEquals('92.243.6.32', $result['ip']);

        $result = $this->sitecheck->check('webmail.mailplatform.eu');
        $this->assertTrue($result->isGreen());
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
