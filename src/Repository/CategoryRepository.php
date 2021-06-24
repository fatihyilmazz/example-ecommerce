<?php

namespace App\Repository;

use App\Entity\Sector;
use App\Entity\Category;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param int $sectorId
     * @param bool $useCache
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\App\Entity\Category
     */
    public function getCategoriesBySectorId(int $sectorId, bool $useCache)
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c', 'sc', 'scsc');

        $queryBuilder = $queryBuilder->leftJoin(
            'c.subCategories',
            'sc',
            Join::WITH,
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('sc.isActive', ':isActive'),
                $queryBuilder->expr()->isNull('sc.deletedAt')
            )
        );

        $queryBuilder = $queryBuilder->leftJoin(
            'sc.subCategories',
            'scsc',
            Join::WITH,
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('scsc.isActive', ':isActive'),
                $queryBuilder->expr()->isNull('scsc.deletedAt')
            )
        );

        $queryBuilder = $queryBuilder
            ->where('c.sector = :sectorId')
            ->andWhere('c.isActive = :isActive')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('c.parent IS NULL')
            ->setParameters([
                'sectorId' => $sectorId,
                'isActive' => true,
            ]);

        if ($useCache) {
            $cacheKey = sprintf('%s%s', Category::CACHE_KEY_BY_SECTOR_ID, $sectorId);

            return new ArrayCollection(
                $queryBuilder->getQuery()
                ->useResultCache(true, Category::CACHE_LIFETIME_ALL, $cacheKey)
                ->getResult()
            );
        }

        return new ArrayCollection(
            $queryBuilder->getQuery()
            ->getResult()
        );
    }


    /**
     * @param string $slug
     * @param Sector $sector
     * @param bool $useCache
     *
     * @return Category|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAvailableBySlug(string $slug, Sector $sector, bool $useCache = true): ?Category
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c', 'sc', 'scsc')
            ->leftJoin('c.subCategories', 'sc')
            ->leftJoin('sc.subCategories', 'scsc')
            ->where('c.slug = :slug')
            ->andWhere('c.isActive = :isActive')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('c.sector = :sectorId')
            ->setParameters([
                'slug' => $slug,
                'isActive' => true,
                'sectorId' => $sector->getId(),
            ]);

        if ($useCache) {
            $cacheKey = sprintf('%s%s', Category::CACHE_KEY_BY_SLUG, md5($slug));

            return $queryBuilder->getQuery()
                ->useResultCache(true, Category::CACHE_LIFETIME_SLUG, $cacheKey)
                ->getOneOrNullResult();
        }

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\App\Entity\Category
     */
    public function getAllActiveCategories()
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }
}
