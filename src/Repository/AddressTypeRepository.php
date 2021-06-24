<?php

namespace App\Repository;

use App\Entity\AddressType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AddressType|null find($id, $lockMode = null, $lockVersion = null)
 * @method AddressType|null findOneBy(array $criteria, array $orderBy = null)
 * @method AddressType[]    findAll()
 * @method AddressType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AddressType::class);
    }

    // /**
    //  * @return AddressType[] Returns an array of AddressType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AddressType
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
