<?php

namespace TGWF\Greencheck\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use TGWF\Greencheck\Entity\GreencheckUrl;

/**
 * GreencheckUrlRepository.
 */
class GreencheckUrlRepository extends EntityRepository
{
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
