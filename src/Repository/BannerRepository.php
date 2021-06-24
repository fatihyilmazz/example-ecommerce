<?php

namespace App\Repository;

use App\Entity\Banner;
use App\Entity\Sector;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Banner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Banner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Banner[]    findAll()
 * @method Banner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Banner::class);
    }

    /**
     * @param Sector $sector
     * @param bool $useCache
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\App\Entity\Banner[]
     */
    public function getBannersBySector(Sector $sector, bool $useCache)
    {
        $queryBuilder = $this->createQueryBuilder('banner')
            ->select('banner')
            ->where('banner.sector = :sectorId')
            ->andWhere('banner.isActive = :isActive')
            ->andWhere('banner.deletedAt IS NULL')
            ->setParameters([
                'sectorId' => $sector->getId(),
                'isActive' => true,
            ])
            ->orderBy('banner.priority', 'ASC');

        if ($useCache) {
            $cacheKey = sprintf('%s%s', Banner::CACHE_KEY_BY_SECTOR, $sector->getId());

            return new ArrayCollection(
                $queryBuilder->getQuery()
                ->useResultCache(true, Banner::CACHE_LIFETIME_ALL, $cacheKey)
                ->getResult()
            );
        }

        return new ArrayCollection(
            $queryBuilder->getQuery()
            ->getResult()
        );
    }
}
