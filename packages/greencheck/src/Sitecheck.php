<?php

namespace TGWF\Greencheck;

use TGWF\Greencheck\Entity\GreencheckAs;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\Repository\GreencheckAsRepositoryInterface;
use TGWF\Greencheck\Repository\GreencheckIpRepositoryInterface;
use TGWF\Greencheck\Repository\GreencheckTldRepositoryInterface;
use TGWF\Greencheck\Repository\GreencheckUrlRepositoryInterface;
use TGWF\Greencheck\Sitecheck\Aschecker;
use TGWF\Greencheck\Sitecheck\Cache;
use TGWF\Greencheck\Sitecheck\DnsFetcher;
use TGWF\Greencheck\Sitecheck\Logger;
use TGWF\Greencheck\Sitecheck\Validator;

/**
 * Sitecheck class.
 *
 * The sitecheck handles all actions with regard to the Green Web Foundation greencheck.
 *
 * Flow :
 * - Check the cached records for an url, if found return
 * - Check the customer records for an url, if found return
 * - Check the ip records for an url, if found return
 * - Check the as records for an url, if found return
 * - None found, then return url = grey
 *
 * @author Arend-Jan Tetteroo <aj@arendjantetteroo.nl>
 */
class Sitecheck
{
    /**
     * Error messages from validation.
     *
     * @var array
     */
    protected $errorMessages;

    /**
     * Should the checks be logged.
     *
     * @var boolean, defaults to true
     */
    protected $logChecks = true;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Sitecheck\Cache
     */
    protected $cache;

    /**
     * Needed for log purposes (website|admin|test|bot).
     *
     * @var string
     */
    protected $calledFrom = 'website';

    /**
     * Ip's for checked url's to skip hostname checks on each call.
     *
     * @var array
     */
    protected $ipForUrl = [];

    /**
     * Ip's for checked url's to skip hostname checks on each call.
     *
     * @var array
     */
    protected $cleanurl = [];

    /**
     * The domains we have knowledge on.
     *
     * @var array
     */
    protected $countryTlds;

    /**
     * @var GreencheckUrlRepositoryInterface
     */
    protected $greencheckUrlRepository;

    /**
     * @var GreencheckIpRepositoryInterface
     */
    protected $greencheckIpRepository;

    /**
     * @var GreencheckAsRepositoryInterface
     */
    protected $greencheckAsRepository;

    /**
     * @var Aschecker
     */
    protected $aschecker;

    /**
     * @var GreencheckTldRepositoryInterface
     */
    private $greencheckTldRepository;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DnsFetcher
     */
    private $dnsFetcher;

    public function __construct(
        GreencheckUrlRepositoryInterface $greencheckUrlRepository,
        GreencheckIpRepositoryInterface $greencheckIpRepository,
        GreencheckAsRepositoryInterface $greencheckAsRepository,
        GreencheckTldRepositoryInterface $greencheckTldRepository,
        Cache $cache,
        Logger $logger,
        $calledfrom = 'website',
        DnsFetcher $dnsFetcher = null
    ) {
        $this->calledFrom = $calledfrom;

        $this->validator = new Validator();
        if (null === $dnsFetcher) {
            $dnsFetcher = new DnsFetcher();
        }
        $this->dnsFetcher = $dnsFetcher;

        $this->greencheckUrlRepository = $greencheckUrlRepository;
        $this->greencheckIpRepository = $greencheckIpRepository;
        $this->greencheckAsRepository = $greencheckAsRepository;
        $this->greencheckTldRepository = $greencheckTldRepository;

        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Check if the given url is a valid Hostname, and if so, check that it returns a valid ip adress.
     *
     * @param null|string $url
     *
     * @return bool
     */
    public function validate(?string $url): bool
    {
        $url = $this->validator->getHostname($url);
        $ips = $this->getIpForUrl($url);

        return $this->validator->validate($url, $ips);
    }

    /**
     * Return eventual validation errors.
     *
     * @return array
     */
    public function getValidateErrors()
    {
        return $this->validator->getValidateErrors();
    }

    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Check the url in the greencheck, flow see above.
     *
     * @param string $url The url to check
     *
     * @return SitecheckResult|false
     */
    public function check($url, $checked_by = '0', $checked_browser = '', $checked_through = '')
    {
        $url = $this->validator->getHostname($url);

        $validurl = $this->validate($url);
        // Incorrect url, then return
        if (false == $validurl) {
            return false;
        }

        if ('' == $checked_through) {
            $checked_through = $this->calledFrom;
        }

        // Result is cached, then return result
        if ($result = $this->getCache('result')->fetch(sha1($url))) {
            $result->setCalledFrom(
                [
                    'checked_by' => $checked_by,
                    'checked_browser' => $checked_browser,
                    'checked_through' => $checked_through,
                    ]
            );
            $result->setCheckedAt(new \DateTime('now'));
            $this->logResult($result);
            $result->setCached(true);

            return $result;
        }

        $result = new SitecheckResult($url, $this->getIpForUrl($url));

        $result->setCalledFrom(
            [
                'checked_by' => $checked_by,
                'checked_browser' => $checked_browser,
                'checked_through' => $checked_through,
            ]
        );

        // Check both www.domain.tld and domain.tld
        if (strpos($url, 'www.') === 0) {
            $strippedurl = substr($url, 4);
        } else {
            $strippedurl = 'www.'.$url;
        }

        // Compensated/Greened by Cleanbits?
        $customerResult = $this->greencheckUrlRepository->checkUrl($url);
        if ($customerResult !== null) {
            return $this->updateCustomerResult($result, $customerResult);
        }

        // Recheck
        $customerResult = $this->greencheckUrlRepository->checkUrl($strippedurl);
        if ($customerResult !== null) {
            return $this->updateCustomerResult($result, $customerResult);
        }

        // Not compensated, check in IP database
        $ipResult = $this->checkIp($url);
        if ($ipResult !== null) {
            $matchtext = $ipResult->getIpStart().' - '.$ipResult->getIpEind();

            return $this->updateResult($result, $matchtext, 'ip', $ipResult);
        }

        // Not compensated, check in AS database
        $asResult = $this->checkAs($url);
        if ($asResult !== null) {
            $matchtext = $asResult->getAsn();

            return $this->updateResult($result, $matchtext, 'as', $asResult);
        }

        // Check if we have hosting providers for this domain
        $result = $this->checkTld($url, $result);

        // Not found, then not green by default
        $result->setGreen(false);
        $this->cache->setItem('result', $url, clone $result);
        $this->logResult($result);

        return $result;
    }

    /**
     * Check if we have data for the tld of this url.
     *
     * @param string          $url
     * @param SitecheckResult $result
     *
     * @return SitecheckResult
     */
    public function checkTld($url, $result)
    {
        $url = $this->validator->getHostname($url);
        $tlds = $this->getCountryTlds();
        $tld = $this->getTldFromUrl($url);
        if (!isset($tlds[$tld])) {
            $result->setData(false);
        }

        return $result;
    }

    /**
     * Get the tld for the given url.
     *
     * @param string $url
     *
     * @return string
     */
    public function getTldFromUrl($url)
    {
        $splittedurl = explode('.', $url);
        $tld = array_pop($splittedurl);

        return $tld;
    }

    /**
     * Get all tld's for which we have data.
     *
     * @return array
     */
    public function getCountryTlds()
    {
        if (!isset($this->countryTlds)) {
            $this->countryTlds = $this->greencheckTldRepository->getTLDsWithData();
        }

        return $this->countryTlds;
    }

    /**
     * Log the request to the logtable, for clientlist and statistics.
     *
     * @param SitecheckResult $result
     */
    public function logResult($result)
    {
        if (!$this->logChecks) {
            return;
        }

        $this->logger->logResult($result);
    }

    /**
     * Get the ip belonging to the given url.
     *
     * @param string $url
     *
     * @return array
     */
    public function getIpForUrl($url): array
    {
        if ($this->validator->isUrlAValidPublicIpAddress($url)) {
            $ip = $url;
            if ($this->validator->isValidIpAddressForType($ip, '4')) {
                // Valid ipv4 adress, assign
                $this->ipForUrl[$url]['ipv4'] = $ip;
                $this->ipForUrl[$url]['ipv6'] = false;
            }
            if ($this->validator->isValidIpAddressForType($ip, '6')) {
                // Valid ipv6 adress, assign
                $this->ipForUrl[$url]['ipv6'] = $ip;
                $this->ipForUrl[$url]['ipv4'] = false;
            }

            return $this->ipForUrl[$url];
        }

        if ($this->validator->isUrlAValidIpAddress($url)) {
            // Valid non public ip adress, return false
            $this->ipForUrl[$url]['ipv4'] = false;
            $this->ipForUrl[$url]['ipv6'] = false;

            return $this->ipForUrl[$url];
        }

        // if we get a null value passed into the sitecheck, it
        // gets coerced to "". We change it here so the return values
        // for $url are in one place
        if ("" == $url) {
            $this->ipForUrl[$url]['ipv4'] = false;
            $this->ipForUrl[$url]['ipv6'] = false;

            return $this->ipForUrl[$url];
        }

        // Real url given, clean it up and get ipadress
        $url = $this->validator->getHostname($url);
        if (!isset($this->ipForUrl[$url])) {
            $hostname = $this->getHostByName($url);

            $ip = $hostname['ip'];
            $this->ipForUrl[$url]['ipv4'] = false;
            if (false !== $ip && $this->validator->isValidIpAddressForType($ip, '4')) {
                $this->ipForUrl[$url]['ipv4'] = $ip;
            }

            $this->ipForUrl[$url]['ipv6'] = $hostname['ipv6'];
        }

        return $this->ipForUrl[$url];
    }

    /**
     * Get the ipadress for a given url by the gethostbyname function, return cached if available.
     *
     * @param string $url
     *
     * @return array
     */
    public function getHostByName($url)
    {
        if ($result = $this->getCache('hostbynamelookups')->fetch(sha1('hostbyname'.$url))) {
            $result['cached'] = true;

            return $result;
        }

        // Ignore dns warnings
        $result = $this->dnsFetcher->getIpAddressesForUrl($url);
        if ($result['ip'] === false && $result['ipv6'] === false) {
           // don't cache, something went wrong with the dns request
           return $result;
        }

        $result['cached'] = false;
        $this->cache->setItem('hostbynamelookups', 'hostbyname'.$url, $result);

        return $result;
    }

    /**
     * Check if ip of given url is in the $searchdata set.
     *
     * @param string $url
     *
     * @return GreencheckIp|null
     */
    public function checkIp($url): ?GreencheckIp
    {
        $ip = $this->getIpForUrl($url);
        if ($ip['ipv4'] !== false) {
            $result = $this->greencheckIpRepository->checkIp($ip['ipv4']);
            if ($result !== null) {
                return $result;
            }
        }

        if ($ip['ipv6'] !== false) {
            $result = $this->greencheckIpRepository->checkIp($ip['ipv6']);
        }

        if (!isset($result)) {
            $result = null;
        }

        return $result;
    }

    /**
     * Check if as of given url is in the $searchdata set.
     *
     * @param string $url
     *
     * @return GreencheckAs|null
     */
    public function checkAs($url): ?GreencheckAs
    {
        $as = $this->getAsForUrl($url);

        if ($as === null) {
            return null;
        }
        foreach ($as['as'] as $asnumber) {
            $result = $this->greencheckAsRepository->checkAs($asnumber);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Get the AS information for the given url.
     *
     * @param string $url
     *
     * @return array
     */
    public function getAsForUrl($url): array
    {
        $aschecker = $this->getAsChecker();

        // Get the ip adress for this url
        $ip = $this->getIpForUrl($url);
        if ($ip['ipv4'] !== false) {
            return $aschecker->getAsForIpv4($ip['ipv4']);
        }

        if ($ip['ipv6'] !== false) {
            return $aschecker->getAsForIpv6($ip['ipv6']);
        }

        return []; // This should not happen
    }

    public function getAsChecker(): Sitecheck\Aschecker
    {
        if ($this->aschecker === null) {
            $this->aschecker = new Sitecheck\Aschecker($this->cache);
        }

        return $this->aschecker;
    }

    /**
     * Disable the logging of checks.
     */
    public function disableLog(): void
    {
        $this->logChecks = false;
    }

    public function disableCache(): void
    {
        $this->cache->disableCache();
    }

    public function resetCache($key): void
    {
        $this->cache->resetCache($key);
    }

    public function setCache($key, $cache = null): void
    {
        $this->cache->setCache($key, $cache);
    }

    public function getCache($key = 'default')
    {
        return $this->cache->getCache($key);
    }

    public function getCacheObject()
    {
        return $this->cache;
    }

    /**
     * Update the sitecheckresult with a hostingprovider enttiy, log the result and cache the result.
     *
     * @param SitecheckResult $result
     * @param string $matchtext
     * @param string $matchtype
     * @param object $matchobject
     *
     * @return SitecheckResult
     */
    private function updateResult($result, $matchtext, $matchtype, $matchobject): SitecheckResult
    {
        // Need an entity and not a proxy object for serializing in the cache
        $hp = $matchobject->getHostingprovider();
        $hpnew = new Entity\Hostingprovider();
        $hpnew->setEntity($hp);

        $result->setGreen(true);
        $result->setMatch($matchobject->getId(), $matchtype, $matchtext);
        $result->setHostingProviderId($hpnew->getId());
        $result->setHostingProvider($hpnew);

        $this->logResult($result);
        $this->cache->setItem('result', $result->getCheckedUrl(), $result);

        return $result;
    }

    private function updateCustomerResult($result, $customerResult)
    {
        $result->setGreen(true);
        $result->setMatch($customerResult->getId(), 'url');
        $this->cache->setItem('result', $result->getCheckedUrl(), $result);
        $this->logResult($result);

        return $result;
    }
}
