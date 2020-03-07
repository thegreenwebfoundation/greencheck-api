<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use TGWF\Greencheck\Entity\GreencheckUrl;
use TGWF\Greencheck\Repository\GreencheckUrlRepositoryInterface;
use TGWF\Greencheck\Repository\GreencheckUrlTrait;

/**
 * GreencheckUrlRepository.
 */
class GreencheckUrlRepository extends ServiceEntityRepository implements GreencheckUrlRepositoryInterface
{
    use GreencheckUrlTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GreencheckUrl::class);
    }
}
