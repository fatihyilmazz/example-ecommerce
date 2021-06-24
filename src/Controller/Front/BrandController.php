<?php

namespace App\Controller\Front;

use App\Service\BrandService;
use App\Service\ProductService;
use App\Entity\MainPageProduct;
use App\Service\MainPageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class BrandController extends AbstractController
{
    /**
     * @var BrandService
     */
    protected $brandService;

    /**
     * @var \App\Service\ProductService
     */
    protected $productService;

    /**
     * @var \App\Service\MainPageService
     */
    protected $mainPageService;

    /**
     * @param BrandService $brandService
     * @param ProductService $productService
     * @param MainPageService $mainPageService
     */
    public function __construct(
        BrandService $brandService,
        ProductService $productService,
        MainPageService $mainPageService
    ) {
        $this->brandService = $brandService;
        $this->productService = $productService;
        $this->mainPageService = $mainPageService;
    }

    /**
     * @Route("/markalar/{brandId}", requirements={"id"="\d+"}, methods={"GET"}, name="front.brand.index")
     *
     * @param Request $request
     *
     * @param int $brandId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, int $brandId)
    {
        $productFilter = $this->brandService->prepareProductFilter($request, $this->getUser(), $brandId);

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['brand', 'medias', 'platforms', 'segmentPrices', 'campaigns']);

        $products = $this->productService->search($productSearchRequest);

        $filters = $this->productService->getProductFilters($productFilter);

        $brand = $this->productService->getBrandById($brandId);

        if (!empty($brand)) {
            $breadcrumbs = $this->brandService->getBreadcrumbs($brand);
        }

        $popularProducts = $this->mainPageService->getMainPageProductsByTypeId(
            $this->getUser(),
            MainPageProduct::TYPE_POPULAR_PRODUCTS
        );

        return $this->render('front/lists/shop.html.twig', [
            'brand' => $brand,
            'breadcrumbs' => $breadcrumbs ?? null,
            'products' => $products['products'],
            'currentPage' => $products['current_page'],
            'totalPage' => $products['total_page'],
            'totalRecord' => $products['total_record'],
            'filters' => $filters,
            'popularProducts' => $popularProducts,
        ]);
    }
}
