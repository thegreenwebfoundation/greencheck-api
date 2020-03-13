<?php

namespace TGWF\Greencheck\Repository;

use Doctrine\ORM\NonUniqueResultException;
use TGWF\Greencheck\Entity\GreencheckUrl;

trait GreencheckUrlTrait
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
    public function checkUrl(string $url): ?GreencheckUrl
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);

        $queryBuilder = $this->createQueryBuilder('i');
        $query = $queryBuilder->where($queryBuilder->expr()->gt('i.datumEind', ':datum'))
            ->andWhere($queryBuilder->expr()->eq('LOWER(i.url)', ':url'))
            ->setParameters(
                [
                    'datum' => $date,
                    'url' => $url,
                ]
            )
            ->getQuery();
        $query->enableResultCache(86400); // cache for 1 day
        return $query->getOneOrNullResult();
    }
}