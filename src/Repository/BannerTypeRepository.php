<?php

namespace App\Repository;

use App\Entity\BannerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BannerType|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannerType|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannerType[]    findAll()
 * @method BannerType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannerTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BannerType::class);
    }
}
