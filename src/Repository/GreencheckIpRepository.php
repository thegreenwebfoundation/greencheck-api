<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use TGWF\Greencheck\Entity\Greencheck;
use TGWF\Greencheck\Entity\GreencheckIp;
use TGWF\Greencheck\Repository\GreencheckIpRepositoryInterface;
use TGWF\Greencheck\Repository\GreencheckIpTrait;

/**
 * GreencheckIpRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GreencheckIpRepository extends ServiceEntityRepository implements GreencheckIpRepositoryInterface
{
    use GreencheckIpTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GreencheckIp::class);
    }
}