<?php


namespace TGWF\Greencheck\Sitecheck;


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

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Log the request to the logtable, for clientlist and statistics.
     *
     * @param SitecheckResult $result
     */
    public function logResult(SitecheckResult $result)
    {
        $match = $result->getMatch();

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
        $gc->setUrl($result->getCheckedUrl());
        $gc->setDatum(new \DateTime('now'));
        $gc->setIp(GreencheckIp::convertIpPresentationToDecimal($ip));

        $this->entityManager->persist($gc);
        $this->entityManager->flush();
    }
}