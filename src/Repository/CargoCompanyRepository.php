<?php

namespace App\Repository;

use App\Entity\CargoCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CargoCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method CargoCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method CargoCompany[]    findAll()
 * @method CargoCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CargoCompanyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CargoCompany::class);
    }

    // /**
    //  * @return CargoCompany[] Returns an array of CargoCompany objects
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
    public function findOneBySomeField($value): ?CargoCompany
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
