<?php


namespace TGWF\Greencheck\Repository;

use TGWF\Greencheck\Entity\GreencheckIp;

trait GreencheckIpTrait
{
    /**
     * Check if ip is in the database as green.
     *
     * @param string $ip
     *
     * @return array|null
     */
    public function checkIp($ip)
    {
        $ipDecimal = GreencheckIp::convertIpPresentationToDecimal($ip);

        $qb = $this->createQueryBuilder('i');

        // Don't use parameters as they will quote the ip as string and then the query won't work
        $query = $qb->where($qb->expr()->lte('i.ipStartLong', $ipDecimal))
            ->andWhere($qb->expr()->gte('i.ipEindLong', $ipDecimal))
            ->andWhere('i.active = 1')
            ->orderBy('i.ipEindLong')
            ->getQuery();
        $query->enableResultCache(3600);
        $result = $query->getResult();
        if ($result) {
            return $result[0];
        }

        return null;
    }
}