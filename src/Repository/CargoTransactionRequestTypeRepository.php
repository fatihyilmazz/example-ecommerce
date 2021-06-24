<?php

namespace App\Repository;

use App\Entity\CargoTransactionRequestType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method CargoTransactionRequestType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CargoTransactionRequestType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CargoTransactionRequestType[]    findAll()
 * @method CargoTransactionRequestType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CargoTransactionRequestTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CargoTransactionRequestType::class);
    }
}
