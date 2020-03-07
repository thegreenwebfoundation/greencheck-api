<?php

namespace TGWF\Greencheck\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GreencheckIpRepository.
 *
 */
class GreencheckIpRepository extends EntityRepository implements GreencheckIpRepositoryInterface
{
    use GreencheckIpTrait;
}
