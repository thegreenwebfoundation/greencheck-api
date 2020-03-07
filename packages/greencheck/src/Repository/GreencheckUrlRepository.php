<?php

namespace TGWF\Greencheck\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use TGWF\Greencheck\Entity\GreencheckUrl;

/**
 * GreencheckUrlRepository.
 */
class GreencheckUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GreencheckUrl::class);
    }

    /**
     * Check if the given url is compensated.
     *
     * @param string $url
     *
     * @return GreencheckUrl
     *
     * @throws NonUniqueResultException
     */
    public function checkUrl($url)
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('i');
        $query = $qb->where($qb->expr()->gt('i.datumEind', ':datum'))
                    ->andWhere($qb->expr()->eq('LOWER(i.url)', ':url'))
                    ->setParameters(
                        [
                                        'datum' => $date,
                                        'url' => $url,
                                      ]
                                )
                ->getQuery();
        $query->useResultCache(true);
        $query->setResultCacheLifetime(86400); // Cache for 1 day
        return $query->getOneOrNullResult();
    }
}
