<?php


namespace TGWF\Greencheck\Sitecheck;

use Doctrine\ORM\EntityManager;
use TGWF\Greencheck\Entity\Greencheck;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\SitecheckResult;
use TGWF\Greencheck\LatestResult;
use Predis\Client;

class Logger
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    public $redis;

    public function __construct(EntityManager $entityManager, Client $redis)
    {
        $this->entityManager = $entityManager;
        $this->redis = $redis;
    }

    /**
     * Log the request to the logtable, for clientlist and statistics.
     *
     * @param SitecheckResult $result
     */
    public function logResult(SitecheckResult $result)
    {
        $match = $result->getMatch();
        $checkedUrl = $result->getCheckedUrl();
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

        $gc = new Greencheck();
        $gc->setIdGreencheck($match['id']);
        if ($result->isHostingProvider()) {
            $gc->setIdHp($result->getHostingProviderId());
        } else {
            $gc->setIdHp(null);
        }
        $gc->setType($match['type']);
        $gc->setGreen($result->isGreen());
        $gc->setUrl($checkedUrl);
        $gc->setDatum(new \DateTime('now'));
        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal($ip));

        $latest = new LatestResult();
        $latest->setResult($result);

        $domainKey = "domains:$checkedUrl";
        $encoded = json_encode($latest);

        $this->redis->set("domains:$checkedUrl", json_encode($latest));

        $this->entityManager->persist($gc);

        $this->entityManager->flush();
    }
}