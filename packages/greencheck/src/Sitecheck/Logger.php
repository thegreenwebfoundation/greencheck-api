<?php


namespace TGWF\Greencheck\Sitecheck;

use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\EntityManager;
use TGWF\Greencheck\Entity\Greencheck;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\SitecheckResult;

class Logger
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager,
        \Redis $redis
        )
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

        $gcJson = json_encode($result);

        $this->entityManager->persist($gc);
        $this->redis->set("domains:$checkedUrl", $gcJson);
        $this->entityManager->flush();
    }
}