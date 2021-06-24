<?php

namespace App\Controller\Admin;

use App\Entity\Sector;
use App\Entity\Merchant;
use App\Entity\ProductComment;
use App\Service\SectorService;
use App\Service\ProductService;
use App\Security\Voter\UserVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\ProductManagement\Request\Product\ProductFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class ProductController extends AbstractController
{
    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @Route("/products/search", methods={"GET"}, name="admin.products.search")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param TranslatorInterface $translator
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function search(Request $request, TranslatorInterface $translator, SectorService $sectorService)
    {
        $limit = $request->query->getInt('limit');
        $sectorId = $request->query->getInt('sectorId', null);

        $sector = $sectorService->findById($sectorId);

        if (!($sector instanceof Sector)) {
            return $this->json([
                'message' => $translator->trans('system.not_found.sector_number'),
            ]);
        }

        $productFilter = new ProductFilter();
        $productFilter->setName($request->query->get('keyword'));
        $productFilter->setMerchantId(Merchant::ID_BIRCOM);
        $productFilter->setLimit($limit);
        $productFilter->setSectorId($sector->getProductManagementSectorId());

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['medias']);

        $searchResult = $this->productService->search($productSearchRequest);

        return $this->json($searchResult);
    }

    /**
     * @Route("/products/comments", methods={"GET"}, name="admin.products.comments.index")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function comments(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->productService->paginateProductsComments($page, $limit);
        $productComments = (array) $paginate->getCurrentPageResults();

        /** @var ProductComment $productComment */
        foreach ($productComments as $key => $productComment) {
            $productFilter = new ProductFilter();
            $productFilter->setProductId($productComment->getProductId());
            $productFilter->setMerchantId($productComment->getMerchant()->getId());
            $productFilter->setSectorId($productComment->getSector()->getId());

            $productSearchRequest = new ProductSearchRequest();
            $productSearchRequest->setFilter($productFilter);
            $productSearchRequest->setIncludes(['medias']);

            $product = $this->productService->getProductById($productSearchRequest);

            if (empty($product)) {
                unset($productComments[$key]);

                continue;
            }

            $productComment->setProduct($product);
        }

        return $this->render('admin/product_comments/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'comments' => $productComments,
        ]);
    }

    /**
     * @Route("/products/comments/{id}",
     *     methods={"GET", "PUT"},
     *     requirements={"id"="\d+"},
     *     name="admin.products.comments.edit")
     *
     * @ParamConverter("productComment", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param ProductComment $productComment
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function editComment(Request $request, ProductComment $productComment, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $productFilter = new ProductFilter();
        $productFilter->setProductId($productComment->getProductId());
        $productFilter->setMerchantId($productComment->getMerchant()->getId());
        $productFilter->setSectorId($productComment->getSector()->getId());

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['medias', 'platforms']);

        $product = $this->productService->getProductById($productSearchRequest);
        if (empty($product)) {
            $this->addFlash('status', 'error');
            $this->addFlash('message', $translator->trans('system.not_found.product'));

            return $this->redirectToRoute('admin.products.comments.index');
        }

        $productComment->setProduct($product);

        if ($request->query->getBoolean('approvedAt', false)) {
            $productComment->setApprovedAt(new \DateTime());

            $productComment = $this->productService->updateProductComment($productComment);

            if ($productComment instanceof ProductComment) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.products.comments.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $translator->trans('system.info.flash_message.error'));
        }


        return $this->render('admin/product_comments/edit.html.twig', [
            'productComment' => $productComment,
        ]);
    }

    /**
     * @Route("/products/comments/{id}/delete",
     *     methods={"DELETE"},
     *     requirements={"id"="\d+"},
     *     name="admin.products.comments.delete")
     *
     * @ParamConverter("productComment", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param ProductComment $productComment
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function delete(Request $request, ProductComment $productComment, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $isDeleted = $this->productService->deleteProductComment($productComment);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', $translator->trans('system.info.flash_message.success'));
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', $translator->trans('system.info.flash_message.error'));
        }

        return $this->redirectToRoute('admin.products.comments.index');
    }
}
