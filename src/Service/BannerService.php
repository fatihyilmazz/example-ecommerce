<?php

namespace App\Service;

use App\Entity\Banner;
use App\Entity\Sector;
use Pagerfanta\Pagerfanta;
use App\Entity\BannerType;
use Psr\Log\LoggerInterface;
use App\Entity\FilterClass\BannerFilter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BannerService extends AbstractService
{
    /**
     * @var \App\Repository\BannerRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $bannerRepository;

    /**
     * @var \App\Repository\BannerTypeRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $bannerTypeRepository;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        parent::__construct($container, $logger);

        $this->bannerRepository = $this->entityManager->getRepository(Banner::class);
        $this->bannerTypeRepository = $this->entityManager->getRepository(BannerType::class);
    }

    /**
     * @param Request $request
     *
     * @return BannerFilter
     */
    public function prepareBannerFilterWithRequest(Request $request): BannerFilter
    {
        $bannerFilter = new BannerFilter();

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', BannerFilter::DEFAULT_LIMIT);

        if ($limit > BannerFilter::DEFAULT_LIMIT) {
            $limit = BannerFilter::DEFAULT_LIMIT;
        }

        $bannerFilter->setPage($page);
        $bannerFilter->setLimit($limit);

        if (!empty($request->query->get('id'))) {
            $bannerFilter->setId($request->query->getInt('id'));
        }

        if (!empty($request->query->get('name'))) {
            $bannerFilter->setName($request->query->get('name'));
        }

        if (!empty($request->query->get('sectorId'))) {
            $bannerFilter->setSectorId($request->query->getInt('sectorId'));
        }

        if ($request->query->getInt('isActive') == 1 || $request->query->getInt('isActive') == 2) {
            $isActive = $request->query->getBoolean('isActive') == 1 ? true : false;
            $bannerFilter->setIsActive($isActive);
        }

        return $bannerFilter;
    }

    /**
     * @param BannerFilter $bannerFilter
     *
     * @return array|Pagerfanta|null
     */
    public function getBannersWithFilter(BannerFilter $bannerFilter)
    {
        try {
            $parameters = null;

            $queryBuilder = $this->bannerRepository->createQueryBuilder('b')
                ->select('b')
                ->where('b.deletedAt IS NULL')
                ->orderBy('b.priority', 'ASC');

            if (!empty($bannerFilter->getId())) {
                $queryBuilder->andWhere('b.id = :bannerId');

                $parameters['bannerId'] = $bannerFilter->getId();
            }

            if (!empty($bannerFilter->getName())) {
                $queryBuilder->andWhere('b.name LIKE :bannerName');

                $parameters['bannerName'] = "%{$bannerFilter->getName()}%";
            }

            if (!empty($bannerFilter->getSectorId())) {
                $queryBuilder->andWhere('b.sector = :sectorId');

                $parameters['sectorId'] = $bannerFilter->getSectorId();
            }

            if (!is_null($bannerFilter->isActive())) {
                $queryBuilder->andWhere('b.isActive = :isActive');

                $parameters['isActive'] = $bannerFilter->isActive();
            }

            if (!empty($parameters)) {
                $queryBuilder->setParameters($parameters);
            }

            $banners = new Pagerfanta(new DoctrineORMAdapter($queryBuilder));
            $banners->setAllowOutOfRangePages(true);
            $banners
                ->setMaxPerPage($bannerFilter->getLimit())
                ->setCurrentPage($bannerFilter->getPage());

            $banners = $this->checkForExpireDate($banners);

            return $banners;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][getBannersWithFilter] %s', $e), [
                'bannerId' => $bannerFilter->getId(),
                'bannerName' => $bannerFilter->getName(),
                'sectorId' => $bannerFilter->getSectorId(),
                'pageNumber' => $bannerFilter->getPage(),
                'limit' => $bannerFilter->getLimit(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][getBannersWithFilter] %s', $e), [
                'bannerId' => $bannerFilter->getId(),
                'bannerName' => $bannerFilter->getName(),
                'sectorId' => $bannerFilter->getSectorId(),
                'pageNumber' => $bannerFilter->getPage(),
                'limit' => $bannerFilter->getLimit(),
            ]);
        }

        return null;
    }

    /**
     * @param Pagerfanta $pagination
     *
     * @return Pagerfanta|null
     */
    public function checkForExpireDate(Pagerfanta $pagination)
    {
        try {
            /** @var Banner $banner */
            foreach ((array)$pagination->getCurrentPageResults() as $banner) {
                if ($banner->isActive() && !is_null($banner->getFinishedAt()) &&
                    $banner->getFinishedAt() < new \DateTime()) {
                    $banner->setIsActive(false);

                    $this->update($banner);
                }
            }

            return $pagination;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][checkForExpireDate] %s', $e), [
                'pagerfanta' => (array)$pagination->getCurrentPageResults(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][checkForExpireDate] %s', $e), [
                'pagerfanta' => (array)$pagination->getCurrentPageResults(),
            ]);
        }

        return null;
    }

    /**
     * @param Sector $sector
     * @param bool $useCache
     *
     * @return \App\Entity\Banner[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBannersBySector(Sector $sector, bool $useCache = true)
    {
        try {
            $banners = $this->bannerRepository->getBannersBySector($sector, $useCache);
            foreach ($banners as $banner) {
                if ((is_null($banner->getStartedAt()) && is_null($banner->getFinishedAt())) ||
                    ($banner->getStartedAt() < new \DateTime() && $banner->getFinishedAt() > new \DateTime() )
                ) {
                    continue;
                }

                $banners->removeElement($banner);
            }

            return $banners;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][getBannersBySector] %s', $e), [
                'sectorId' => $sector->getId(),
                'useCache' => $useCache,
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][getBannersBySector] %s', $e), [
                'sectorId' => $sector->getId(),
                'useCache' => $useCache,
            ]);
        }

        return new ArrayCollection();
    }

    /**
     * @param Banner $banner
     *
     * @return Banner|null
     */
    public function create(Banner $banner)
    {
        try {
            $banner->setPriority(0);

            $this->entityManager->persist($banner);
            $this->entityManager->flush($banner);

            return $banner;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][create] %s', $e), [
                'name' => $banner->getName(),
                'sectorId' => $banner->getSector()->getId(),
                'bannerTypeId' => $banner->getBannerType()->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][create] %s', $e), [
                'name' => $banner->getName(),
                'sectorId' => $banner->getSector()->getId(),
                'bannerTypeId' => $banner->getBannerType()->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Banner $banner
     *
     * @return Banner|null
     */
    public function update(Banner $banner)
    {
        try {
            $this->entityManager->persist($banner);
            $this->entityManager->flush($banner);

            return $banner;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][update] %s', $e), [
                'bannerId' => $banner->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][update] %s', $e), [
                'bannerId' => $banner->getId(),
            ]);
        }

        return null;
    }

    /**
     * @return BannerType[]|array|object[]
     */
    public function getAllBannerTypes()
    {
        return $this->bannerTypeRepository->findAll();
    }

    /**
     * @param Banner $banner
     *
     * @return bool
     */
    public function delete(Banner $banner): bool
    {
        try {
            $banner->setDeletedAt(new \DateTime());

            $this->entityManager->flush($banner);

            return true;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][delete] %s', $e), [
                'bannerId' => $banner->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][delete] %s', $e), [
                'bannerId' => $banner->getId(),
            ]);
        }

        return false;
    }

    /**
     * @param array $bannerOrders
     *
     * @return Banner|object|null
     */
    public function sortBanners(array $bannerOrders)
    {
        try {
            foreach ($bannerOrders as $bannerOrder) {
                $banner = $this->bannerRepository->findOneBy(['id' => $bannerOrder['id']]);
                $banner->setPriority($bannerOrder['order']);
                $this->entityManager->persist($banner);
            }
            $this->entityManager->flush();

            return $banner;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BannerService][sortBanners] %s', $e), [
                'bannerOrders' => $bannerOrders,
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BannerService][sortBanners] %s', $e), [
                'bannerOrders' => $bannerOrders,
            ]);
        }

        return null;
    }
}
