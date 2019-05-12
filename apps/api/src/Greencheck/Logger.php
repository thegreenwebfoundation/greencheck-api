<?php

namespace App\Greencheck;

use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;
use TGWF\Greencheck\Entity\Greencheck;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\PublicSuffix\ManageSLD;
use TGWF\PublicSuffix\RedisRuleStoreSLD;
use TGWF\Greencheck\SitecheckResult;

class Logger
{
    protected $em = null;
    protected $output = null;
    protected $manageSLD = null;
    protected $gcip = null;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Client
     */
    public $redis;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logger constructor.
     * @param EntityManagerInterface $entityManager
     * @param Client $redis
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Client $redis,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->redis = $redis;
        $this->logger = $logger;
    }

    /**
     * Log the request to the logtable, for clientlist and statistics.
     *
     * @param SitecheckResult $result
     */
    public function logResult(SitecheckResult $result)
    {
        if ('test' == $result->getCalledFrom('checked_through')) {
            $this->logger->debug('Test result, not logging');

            return;
        }

        $match = $result->getMatch();

        // Make sure we don't have '\' in urls
        $checked_url = str_replace('\\', '', $result->getCheckedUrl());

        $tld = $this->getTld($checked_url);

        // No matches, then assign none for logging
        if (!isset($match['id'])) {
            $match['id'] = 0;
            $match['type'] = 'none';
        }

        if ($result->getIpAddress()) {
            $ip = $result->getIpAddress();
        } elseif ($result->getIpAddress('ipv6')) {
            $ip = $result->getIpAddress('ipv6');
        } else {
            $ip = 0;
        }
        $ip = GreencheckIp::convertIpPresentationToDecimal($ip);

        $gc = new Greencheck();
        $gc->setIdGreencheck($match['id']);
        if ($result->isHostingProvider()) {
            $gc->setIdHp($result->getHostingProviderId());
        } else {
            $gc->setIdHp(0);
        }
        $gc->setType($match['type']);
        $gc->setGreen($result->isGreen());
        $gc->setUrl($checked_url);
        $gc->setDatum($result->getCheckedAt());
        $gc->setIp($ip);
        $gc->setTld($tld);
        $this->entityManager->persist($gc);

        $latest = new LatestResult();
        $latest->setResult($result);

        // TODO - see about using this logger of the one in the
        // greencheck library
        $this->redis->set("domains:$checkedUrl", json_encode($latest));

        $this->redis->lpush('latest_checks', json_encode($latest));
        $this->redis->ltrim('latest_checks', 0, 999);
    }

    /**
     * Get the tld from the public suffic list.
     *
     * @param string $url Url to get TLD from
     *
     * @return string TLD from public suffix list
     */
    public function getTld($url)
    {
        $manageSLD = $this->initializeRuleStore();
        list($sld, $label, $rest, $registerable, $pattern, $flags) = $manageSLD->lookup($url);

        return $sld;
    }

    public function initializeRuleStore()
    {
        if (null !== $this->manageSLD) {
            return $this->manageSLD;
        }

        $store = new RedisRuleStoreSLD($this->redis);
        $manageSLD = new ManageSLD($store);

        $meta = $store->getMeta();
        $now = time();
        $refreshcache = (!$meta || ($now - $meta->timestamp) > 86400); // Refresh if empty or once every 1 day
        if ($refreshcache) {
            $manageSLD->parseToStore();
            $parseOK = $manageSLD->parseFile();
            if ($parseOK) {
                $store->save($manageSLD);
            }
        }

        return $this->manageSLD = $manageSLD;
    }
}
