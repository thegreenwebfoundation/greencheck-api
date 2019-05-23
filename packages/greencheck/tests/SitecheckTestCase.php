<?php
require_once __DIR__ . '/TestConfiguration.php';

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use TGWF\Greencheck\Sitecheck;

use PHPUnit\Framework\TestCase;
use Predis\Client;

class SitecheckTestCase extends TestCase
{
    /**
     *
     * @var Sitecheck
     */
    protected $sitecheck = null;

    protected $redis = null;

    public function setUp() :void
    {
        // reset database to known state
        TestConfiguration::setupDatabase();

        $config     = TestConfiguration::$config;
        $entityManager   = TestConfiguration::$em;
        $this->em = $entityManager;

        $this->cache = $this->setupCache();
        $this->sitecheck = $this->setupSitechecker($entityManager, $config, $this->cache);

        //Cleanup all cache entries to correctly test
        $this->cache->reset();
    }

    public function setupCache()
    {
        // Setup the cache
        return new ArrayAdapter(

        // the default lifetime (in seconds) for cache items that do not define their
        // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
        // until the current PHP process finishes)
            $defaultLifetime = 0,

            // if ``true``, the values saved in the cache are serialized before storing them
            $storeSerialized = true
        );
    }

    /**
     * @param $entityManager
     * @param $config
     * @return ArrayAdapter
     * @throws ReflectionException
     */
    public function setupSitechecker($entityManager, $config, $cache)
    {
        $redis = new Client([
            "host" => TestConfiguration::$config['greencheck']['redis']['host']
        ]);
        $this->redis = $redis;

        // @todo mock these where needed
        $greencheckUrlRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $greencheckIpRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $greencheckAsRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $greencheckTldRepository = $entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $dns = $this->createMock(Sitecheck\DnsFetcher::class);
        $dns->method('getIpAddressesForUrl')->will($this->returnValueMap(TestConfiguration::getIpUrlMapping()));

        return new Sitecheck($greencheckUrlRepository, $greencheckIpRepository, $greencheckAsRepository, $greencheckTldRepository, $cache, 'test', $dns);
    }
}
