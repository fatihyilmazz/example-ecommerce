<?php

namespace App\Controller\Admin;

use App\Entity\Merchant;
use App\Entity\MainPageProduct;
use App\Entity\Sector;
use App\Service\ProductService;
use App\Service\MainPageService;
use App\Security\Voter\UserVoter;
use App\Entity\MainPageProductType;
use App\Service\SectorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProductManagement\Request\Product\ProductFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class MainPageController extends AbstractController
{
    /**
     * @var MainPageService
     */
    protected $mainPageService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * MainPageController constructor.
     * @param MainPageService $mainPageService
     * @param ProductService $productService
     * @param SectorService $sectorService
     */
    public function __construct(MainPageService $mainPageService, ProductService $productService, SectorService $sectorService)
    {
        $this->mainPageService = $mainPageService;
        $this->productService = $productService;
        $this->sectorService = $sectorService;
    }

    /**
     * @Route("/main-pages/types/{typeId}", requirements={"typeId"="\d+"}, methods={"GET"}, name="admin.main_page.types.index")
     *
     * @ParamConverter("mainPageProductType", options={"mapping"={"typeId"="id", null="deletedAt"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param MainPageProductType $mainPageProductType
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, MainPageProductType $mainPageProductType)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        return $this->render('admin/main_pages/products/index.html.twig', [
            'mainPageProductType' => $mainPageProductType,
            'mainPageProducts' => $this->mainPageService->getMainPageProductsByTypeId(
                $this->getUser(),
                $mainPageProductType->getId()
            ),
        ]);
    }

    /**
     * @Route("/main-pages/products/create", methods={"GET", "POST"}, name="admin.main_page.products.create")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $mainPageProduct = new MainPageProduct();

        $form = $this->createForm(\App\Form\MainPageProductType::class, $mainPageProduct, [
            'action' => $this->generateUrl('admin.main_page.products.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainPageProduct = $this->mainPageService->create($mainPageProduct);
            if (!($mainPageProduct instanceof MainPageProduct)) {
                $this->addFlash('status', 'error');
                $this->addFlash('message', 'İşlem Başarısız!');
            } else {
                $this->addFlash('status', 'success');
                $this->addFlash('message', 'İşlem Başarılı!');

                return $this->redirectToRoute('admin.main_page.types.index', ['typeId' => $mainPageProduct->getMainPageProductType()->getId()]);
            }
        }

        return $this->render('admin/main_pages/products/create.html.twig', [
            'form' => $form->createView(),
            'sectors' => $this->sectorService->getAll(),
            'mainPageProductTypes' => $this->mainPageService->getAllMainPageProductTypes(),
        ]);
    }

    /**
     * @Route("/main-pages/products/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.main_page.products.edit")
     *
     * @ParamConverter("mainPageProduct", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\MainPageProduct $mainPageProduct
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, MainPageProduct $mainPageProduct)
    {
        $form = $this->createForm(\App\Form\MainPageProductType::class, $mainPageProduct, [
            'action' => $this->generateUrl('admin.main_page.products.edit', ['id' => $mainPageProduct->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $productFilter = new ProductFilter();
        $productFilter->setProductId($mainPageProduct->getProductId());
        $productFilter->setMerchantId(Merchant::ID_BIRCOM);
        $productFilter->setSectorId($mainPageProduct->getSector()->getProductManagementSectorId());

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['medias', 'categories', 'platforms', 'segmentPrices', 'specs']);

        $product = $this->productService->getProductById($productSearchRequest, null, false);
        if (empty($product)) {
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');

            return $this->redirectToRoute('admin.main_page.types.index', ['typeId' => $mainPageProduct->getMainPageProductType()->getId()]);
        }

        $mainPageProduct->setProduct($product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainPageProduct = $this->mainPageService->update($mainPageProduct);
            if (!($mainPageProduct instanceof MainPageProduct)) {
                $this->addFlash('status', 'error');
                $this->addFlash('message', 'İşlem Başarısız!');
            } else {
                $this->addFlash('status', 'success');
                $this->addFlash('message', 'İşlem Başarılı!');

                return $this->redirectToRoute('admin.main_page.types.index', ['typeId' => $mainPageProduct->getMainPageProductType()->getId()]);
            }
        }

        return $this->render('admin/main_pages/products/edit.html.twig', [
            'form' => $form->createView(),
            'mainPageProduct' => $mainPageProduct,
            'sectors' => $this->sectorService->getAll(),
            'mainPageProductTypes' => $this->mainPageService->getAllMainPageProductTypes(),
        ]);
    }

    /**
     * @Route("/main-pages/products/{id}", requirements={"id"="\d+"}, methods={"DELETE"}, name="admin.main_page.products.delete")
     *
     * @ParamConverter("mainPageProduct", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\MainPageProduct $mainPageProduct
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, MainPageProduct $mainPageProduct)
    {
        $isDeleted = $this->mainPageService->hardDelete($mainPageProduct);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', 'İşlem Başarılı!');
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        return $this->redirectToRoute('admin.main_page.types.index', ['typeId' => $mainPageProduct->getMainPageProductType()->getId()]);
    }

    /**
     * @Route("/main-pages/sort", methods={"PUT"}, name="admin.main_page.sort")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sortMainPageProducts(Request $request)
    {
        $mainPageProducts = json_decode($request->getContent(), true);

        $mainPageProduct = $this->mainPageService->sortMainPageProducts($mainPageProducts);

        if ($mainPageProduct instanceof MainPageProduct) {
            return $this->json([
                'status' => 'success',
                'message' => "Sıramala Güncellendi",
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => "Sıramala Güncellenemedi",
        ]);
    }

}
