<?php

namespace App\Repository;

use App\Entity\CargoTransactionRequest;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method CargoTransactionRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CargoTransactionRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CargoTransactionRequest[]    findAll()
 * @method CargoTransactionRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CargoTransactionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CargoTransactionRequest::class);
    }
}
