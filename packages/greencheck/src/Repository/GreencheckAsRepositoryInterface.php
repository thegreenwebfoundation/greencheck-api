<?php

namespace TGWF\Greencheck\Repository;

use TGWF\Greencheck\Entity\GreencheckAs;

/**
 * GreencheckAsRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
interface GreencheckAsRepositoryInterface
{
    /**
     * Check if AS is in the database as green.
     *
     * @param int $as
     *
     * @return GreencheckAs|null
     */
    public function checkAs(int $as): ?GreencheckAs;
}