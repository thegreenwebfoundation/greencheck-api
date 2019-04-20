<?php
require_once __DIR__ . '/TestConfiguration.php';

use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Table;
use TGWF\Greencheck\Logger\SQLLogger;

use Symfony\Component\Validator\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class SitecheckValidationTest extends TestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    public function setUp(): void
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

        // include the map as an external file ,
        // we use it in multiple files
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
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testGetHostnameFromUrlsShouldReturnCorrectHostname()
    {
        $validator = $this->sitecheck->getValidator();

        $this->assertEquals('www.cleanbits.nl', $validator->getHostname('www.cleanbits.nl'));
        $this->assertEquals('www.cleanbits.net', $validator->getHostname('http://www.cleanbits.net'));
        $this->assertEquals('www.cleanbits.net', $validator->getHostname('https://www.cleanbits.net'));
        $this->assertEquals('www.gmail.co.uk', $validator->getHostname('https://www.gmail.co.uk'));
        $this->assertEquals('192.168.1.1', $validator->getHostname('https://192.168.1.1'));
        $this->assertEquals('192.168.1.1', $validator->getHostname('192.168.1.1'));
        $this->assertEquals('www.ipv6.xs4all.nl', $validator->getHostname('https://www.ipv6.xs4all.nl'));
        $this->assertEquals('2001:888::18:0:0:0:80', $validator->getHostname('http://[2001:888::18:0:0:0:80]'));
        $this->assertEquals('2001:888::18:0:0:0:80', $validator->getHostname('2001:888::18:0:0:0:80'));
        $this->assertEquals('www.ipv6.xs4all.nl', $validator->getHostname('https://www.ipv6.xs4all.nl/balbal'));
        $this->assertEquals('www.ipv6.xs4all.nl', $validator->getHostname('https://www.ipv6.xs4all.nl:80/balbal'));
        $this->assertEquals('2001:888::18:0:0:0:80', $validator->getHostname('http://[2001:888::18:0:0:0:80]:80/blablab'));
        $this->assertEquals(false, $validator->getHostname(null));
    }

    /**
     * Validating an invalid url should return false when validated
     * @return void
     */
    public function testValidatingAnNullUrlShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->validate('null'));
        $this->assertFalse($this->sitecheck->validate(null));
    }

    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidUrlShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('www.cleanbits.nl'));
    }
    
    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidHttpUrlShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('http://www.cleanbits.nl'));
    }
    
    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidFullUrlShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('http://www.cleanbits.nl/en/test'));
    }
    
    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidSecureUrlShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('https://www.cleanbits.nl'));
        $this->assertTrue($this->sitecheck->validate('https://www.xs4all.nl'));
    }
    
    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidProtocolUrlShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('gopher://www.cleanbits.nl'));
    }
    
    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAPrivateIpAdressShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->validate('http://10.0.0.1'));
        $this->assertFalse($this->sitecheck->validate('http://172.16.0.1'));
        $this->assertFalse($this->sitecheck->validate('http://192.168.0.1'));
    }

    /**
     * No error messages should be returned for a valid url that is validated
     * @return void
     */
    public function testGetErrorMessagesForValidUrlShouldReturnNull()
    {
        $this->sitecheck->validate('www.cleanbits.nl');
        $this->assertNull($this->sitecheck->getValidateErrors());
    }

    /**
     * Validating an invalid url should return false when validated
     * @return void
     */
    public function testValidatingAnInvalidUrlShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->validate('a.b.c'));
    }

    /**
     * Validating an invalid url should return false when validated
     * @return void
     */
    public function testValidatingAnNonExistingUrlShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->validate('www.nonexistingurlblablalba.nl'));
        $messages = $this->sitecheck->getValidateErrors();
        $this->assertNotNull($messages);
        $this->assertEquals('No ip adress found for this url', $messages['invalidUrl']);
    }

    /**
      * Checking an invalid url should return false when checked
      * @return void
      */
    public function testCheckingAnNonExistingUrlShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->check('www.nonexistingurlblablalba.nl'));
        $messages = $this->sitecheck->getValidateErrors();
        $this->assertNotNull($messages);
        $this->assertEquals('No ip adress found for this url', $messages['invalidUrl']);
    }

    /**
       * Checking an invalid url should return false when checked
       * @return void
       */
    public function testCheckingANullUrlShouldReturnFalse()
    {
        $this->assertFalse($this->sitecheck->validate(null));
        $this->assertFalse($this->sitecheck->check(null));
        $messages = $this->sitecheck->getValidateErrors();
        $this->assertNotNull($messages);
        $this->assertEquals('The input does not match the expected structure for a DNS hostname', $messages['hostnameInvalidHostname']);
    }

    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidIpv4AdressShouldReturnTrue()
    {
        $this->assertTrue($this->sitecheck->validate('http://www.xs4all.nl'));
        $this->assertTrue($this->sitecheck->validate('http://95.48.1.1'));
        $this->assertTrue($this->sitecheck->validate('95.48.1.1'));
    }

    /**
     * Before we can check an url, we need to validate the url to check if it's a correct url
     *
     * @return void
     */
    public function testValidatingAValidIpv6AdressShouldReturnTrue()
    {
        $this->markTestIncomplete(
            'This website has moved hosts, so we need a different fixture'
        );
        $this->assertTrue($this->sitecheck->validate('http://www.ipv6.xs4all.nl'));
        $this->assertTrue($this->sitecheck->validate('http://[2001:888::18:0:0:0:80]'));
        $this->assertTrue($this->sitecheck->validate('2001:888::18:0:0:0:80'));
    }

    /**
     * The error messages should be filled when a non valid url is validated
     * @return void
     */
    public function testGetErrorMessagesForInvalidUrlShouldReturnMessages()
    {
        $this->sitecheck->validate('a.b.c');
        $messages = $this->sitecheck->getValidateErrors();
        $this->assertNotNull($messages);
    }
}
