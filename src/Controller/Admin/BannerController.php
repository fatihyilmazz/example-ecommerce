<?php

namespace App\Controller\Admin;

use App\Entity\Banner;
use App\Form\BannerType;
use App\Service\CdnService;
use App\Service\BannerService;
use App\Service\SectorService;
use App\Security\Voter\UserVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BannerController extends AbstractController
{
    /**
     * @var BannerService
     */
    protected $bannerService;

    /**
     * @var CdnService
     */
    protected $cdnService;

    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @param BannerService $bannerService
     * @param CdnService $cdnService
     * @param SectorService $sectorService
     */
    public function __construct(BannerService $bannerService, CdnService $cdnService, SectorService $sectorService)
    {
        $this->bannerService = $bannerService;
        $this->cdnService = $cdnService;
        $this->sectorService = $sectorService;
    }

    /**
     * @Route("/banners", methods={"GET"}, name="admin.banners.index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $bannerFilter = $this->bannerService->prepareBannerFilterWithRequest($request);

        $bannerPaginate = $this->bannerService->getBannersWithFilter($bannerFilter);

        return $this->render('admin/banners/index.html.twig', [
            'currentPage' => $bannerPaginate->getCurrentPage(),
            'totalPage' => $bannerPaginate->getNbPages(),
            'totalRecord' => $bannerPaginate->getNbResults(),
            'banners' => (array) $bannerPaginate->getCurrentPageResults(),
            'bannerFilter' => $bannerFilter,
            'sectors' => $this->sectorService->getAll()
        ]);
    }

    /**
     * @Route("/banners/create", methods={"GET", "POST"}, name="admin.banners.create")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $banner = new Banner();

        $form = $this->createForm(BannerType::class, $banner, [
            'action' => $this->generateUrl('admin.banners.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $banner = $this->cdnService->uploadBanner($request->files->get('imageUrl'), $banner);

            if ($banner instanceof Banner) {
                $banner = $this->bannerService->create($banner);

                if ($banner instanceof Banner) {
                    $this->addFlash('status', 'success');
                    $this->addFlash('message', 'İşlem Başarılı!');

                    return $this->redirectToRoute('admin.banners.index');
                } else {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', 'İşlem Başarısız!');
                }
            } else {
                $this->addFlash('status', 'error');
                $this->addFlash('message', 'İşlem Başarısız! Banner yüklenirken bir hata oluştu.');
            }
        }

        return $this->render('admin/banners/create.html.twig', [
            'form' => $form->createView(),
            'sectors' => $this->sectorService->getAll(),
            'bannerTypes' => $this->bannerService->getAllBannerTypes(),
        ]);
    }

    /**
     * @Route("/banners/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.banners.edit")
     *
     * @ParamConverter("banner", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Banner $banner
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Banner $banner)
    {
        $form = $this->createForm(BannerType::class, $banner, [
            'action' => $this->generateUrl('admin.banners.edit', ['id' => $banner->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (($request->files->get('imageUrl') instanceof File)) {
                $banner = $this->cdnService->uploadBanner($request->files->get('imageUrl'), $banner);
            }

            if ($banner instanceof Banner) {
                $banner = $this->bannerService->update($banner);

                if ($banner instanceof Banner) {
                    $this->addFlash('status', 'success');
                    $this->addFlash('message', 'İşlem Başarılı!');

                    return $this->redirectToRoute('admin.banners.index');
                } else {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', 'İşlem Başarısız!');
                }
            } else {
                $this->addFlash('status', 'error');
                $this->addFlash('message', 'İşlem Başarısız!');
            }
        }

        return $this->render('admin/banners/edit.html.twig', [
            'form' => $form->createView(),
            'banner' => $banner,
            'sectors' => $this->sectorService->getAll(),
            'bannerTypes' => $this->bannerService->getAllBannerTypes(),
        ]);
    }

    /**
     * @Route("/banners/{id}", requirements={"id"="\d+"}, methods={"DELETE"}, name="admin.banners.delete")
     *
     * @ParamConverter("banner", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Banner $banner
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Banner $banner)
    {
        $isDeleted = $this->bannerService->delete($banner);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', 'İşlem Başarılı!');
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        return $this->redirectToRoute('admin.banners.index');
    }

    /**
     * @Route("/banners/sort", methods={"PUT"}, name="admin.banners.sort")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sortBanners(Request $request)
    {
        $bannerOrders = json_decode($request->getContent(), true);

        $banner = $this->bannerService->sortBanners($bannerOrders);

        if ($banner instanceof Banner) {
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
