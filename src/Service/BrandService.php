<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\ProductManagement\Request\Product\ProductFilter;

class BrandService extends AbstractService
{
    /**
     * @var \App\Service\ProductManagement\ProductService
     */
    protected $productService;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param ProductService $productService
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        ProductService $productService
    ) {
        parent::__construct($container, $logger);

        $this->productService = $productService;
    }

    /**
     * @param array $brand
     *
     * @return array
     */
    public function getBreadcrumbs(array $brand)
    {
        try {
            $router = $this->container->get('router');

            return [
                [
                    'url' => $router->generate('front.home.index'),
                    'name' => 'Anasayfa',
                ],
                [
                    'url' => $router->generate('front.brand.index', ['brandId' => $brand['id']]),
                    'name' => $brand['name'],
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BrandService][getBreadcrumbs] %s', $e), [
                'brandId' => $brand['id'],
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BrandService][getBreadcrumbs] %s', $e), [
                'brandId' => $brand['id'],
            ]);
        }

        return null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User $user
     * @param int $brandId
     *
     * @return ProductFilter
     */
    public function prepareProductFilter(Request $request, User $user, int $brandId): ProductFilter
    {
        $productFilter = new ProductFilter();
        $productFilter->setSectorId($user->getDefaultSector()->getProductManagementSectorId());
        $productFilter->setBrandIds([$brandId]);
        $productFilter->setSegmentId($user->getMerchant()->getSegmentId());
        $productFilter->setCurrentGroupId($user->getMerchant()->getCurrentGroupId());
        $productFilter->setBuyerMerchantId($user->getMerchant()->getId());
        $productFilter->setBuyerUserId($user->getId());

        if ($request->query->getInt('page') > 0) {
            $productFilter->setPage($request->query->getInt('page'));
        }

        if ($request->query->getBoolean('inStock', false)) {
            $productFilter->setInStock(true);
        }

        if (in_array($request->query->get('sortByPrice'), ['ASC', 'DESC'])) {
            $productFilter->setSortByPrice($request->query->get('sortByPrice'));
        }

        if (in_array($request->query->get('sortByName'), ['ASC', 'DESC'])) {
            $productFilter->setSortByName($request->query->get('sortByName'));
        }

        if ($request->query->get('specValueIds')) {
            $productFilter->setSpecValueIds(explode(',', $request->query->get('specValueIds')));
        }

        if ($request->query->get('minPrice')) {
            $productFilter->setMinPrice($request->query->get('minPrice'));
        }

        if ($request->query->get('maxPrice')) {
            $productFilter->setMaxPrice($request->query->get('maxPrice'));
        }

        if ($request->query->get('keyword')) {
            $productFilter->setName($request->query->get('keyword'));
        }

        return $productFilter;
    }
}
