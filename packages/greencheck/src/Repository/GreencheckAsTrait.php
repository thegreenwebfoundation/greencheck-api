<?php


namespace TGWF\Greencheck\Repository;

use Doctrine\ORM\QueryBuilder;
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
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder('i');
        $query = $queryBuilder
            ->where($queryBuilder->expr()->eq('i.asn', ':as'))
            ->andWhere($queryBuilder->expr()->eq('i.active','true'))
            ->setParameters(['as' => $as])
            ->getQuery();
        $query->enableResultCache(3600);

        return $query->getOneOrNullResult();
    }
}