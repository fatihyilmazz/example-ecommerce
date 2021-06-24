<?php

namespace App\Repository;

use App\Entity\CargoTransactionRequestStatus;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method CargoTransactionRequestStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CargoTransactionRequestStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CargoTransactionRequestStatus[]    findAll()
 * @method CargoTransactionRequestStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CargoTransactionRequestStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CargoTransactionRequestStatus::class);
    }
}
