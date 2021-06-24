<?php

namespace App\Repository;

use App\Entity\ComparedProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ComparedProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComparedProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComparedProduct[]    findAll()
 * @method ComparedProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComparedProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ComparedProduct::class);
    }
}
