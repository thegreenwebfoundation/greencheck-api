<?php

namespace TGWF\Greencheck\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GreencheckUrlRepository.
 */
class GreencheckUrlRepository extends EntityRepository implements GreencheckUrlRepositoryInterface
{
    use GreencheckUrlTrait;
}
