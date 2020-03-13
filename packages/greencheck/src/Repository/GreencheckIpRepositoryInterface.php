<?php

namespace TGWF\Greencheck\Repository;


use TGWF\Greencheck\Entity\GreencheckIp;

/**
 * GreencheckIpRepository.
 *
 */
interface GreencheckIpRepositoryInterface
{
    /**
     * Check if ip is in the database as green.
     *
     * @param string $ip
     *
     * @return GreencheckIp|null
     */
    public function checkIp($ip): ?GreencheckIp;
}