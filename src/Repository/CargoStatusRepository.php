<?php

namespace App\Repository;

use App\Entity\CargoStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CargoStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CargoStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CargoStatus[]    findAll()
 * @method CargoStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CargoStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CargoStatus::class);
    }

    // /**
    //  * @return CargoStatus[] Returns an array of CargoStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CargoStatus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
