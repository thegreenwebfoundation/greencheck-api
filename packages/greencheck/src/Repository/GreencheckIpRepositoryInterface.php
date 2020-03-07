<?php

namespace TGWF\Greencheck\Repository;


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
     * @return array|null
     */
    public function checkIp($ip);
}