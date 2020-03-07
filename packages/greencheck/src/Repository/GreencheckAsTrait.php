<?php


namespace TGWF\Greencheck\Repository;

use TGWF\Greencheck\Entity\GreencheckAs;

trait GreencheckAsTrait
{
    /**
     * Check if AS is in the database as green.
     *
     * @param int $as
     *
     * @return GreencheckAs|null
     */
    public function checkAs(int $as): ?GreencheckAs
    {
        $qb = $this->createQueryBuilder('i');
        $query = $qb
            ->where($qb->expr()->eq('i.asn', ':as'))
            ->andWhere('i.active = 1')
            ->setParameters(['as' => $as])
            ->getQuery();
        $query->enableResultCache(3600);

        return $query->getOneOrNullResult();
    }
}