<?php

namespace App\Controller\Admin;

use App\Entity\Sector;
use App\Entity\User;
use App\Form\SectorType;
use App\Service\SectorService;
use App\Security\Voter\UserVoter;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

class SectorController extends AbstractAdminController
{
    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param SectorService $sectorService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        SectorService $sectorService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->serializer = $serializer;
        $this->sectorService = $sectorService;
    }

    /**
     * @Route("/sectors", methods={"GET"}, name="admin.sectors.index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->sectorService->paginate($page, $limit);

        return $this->render('admin/sectors/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'sectors' => (array)$paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("/sectors/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.sectors.edit")
     *
     * @ParamConverter("sector", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Sector $sector
     * @param \App\Service\ProductManagement\SectorService $pmSectorService
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit(Request $request, Sector $sector, \App\Service\ProductManagement\SectorService $pmSectorService, TranslatorInterface $translator)
    {

        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $form = $this->createForm(SectorType::class, $sector, [
            'action' => $this->generateUrl('admin.sectors.edit', ['id' => $sector->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainCategoryStatus = $this->sectorService->checkMainCategories($sector);

            if ($mainCategoryStatus) {
                $sector = $this->sectorService->updateSectorAndCategories($sector);

                if ($sector instanceof Sector) {
                    $this->addFlash('status', 'success');
                    $this->addFlash('message', 'İşlem Başarılı!');

                    return $this->redirectToRoute('admin.sectors.index');
                } else {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', 'İşlem Başarısız!');
                }
            } else {
                $form->get('categories')->addError(new FormError($translator->trans('system.sector.main_category.not_found')));
            }
        }

        return $this->render('admin/sectors/edit.html.twig', [
            'form' => $form->createView(),
            'sector' => $sector,
            'platformSectors' => $pmSectorService->getPlatformSectors(),
            'categories' => $sector instanceof Sector && !empty($sector->getProductManagementSectorId()) ?
                $pmSectorService->getCategories($sector->getProductManagementSectorId())['categories'] : [],
            'registeredSectors' => $this->sectorService->getAllUndeleted(),
        ]);
    }

    /**
     * @Route("/sectors/{id}/sms-users", requirements={"id"="\d+"}, methods={"GET"}, name="admin.sectors.users")
     *
     * @ParamConverter("sector", options={"mapping"={"id"="id"}})
     *
     * @param Sector $sector
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSectorsSmsUsers(Sector $sector, SectorService $sectorService)
    {
        return $this->json([
            'users' => $sectorService->getSectorSmsUsers($sector),
        ]);
    }

    /**
     * @Route("/sectors/create", methods={"GET", "POST"}, name="admin.sectors.create")
     *
     * @param Request $request
     * @param \App\Service\ProductManagement\SectorService $pmSectorService
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, \App\Service\ProductManagement\SectorService $pmSectorService, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $sector = new Sector();

        $form = $this->createForm(SectorType::class, $sector, [
            'action' => $this->generateUrl('admin.sectors.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        $platformSectors = $pmSectorService->getPlatformSectors();

        if ($form->isSubmitted() && $form->isValid()) {
            $mainCategoryStatus = $this->sectorService->checkMainCategories($sector);

            if ($mainCategoryStatus == true) {
                foreach ($platformSectors as $platformSector) {
                    if ($platformSector['id'] == $request->get('sectorId')) {
                        $sector->setName($platformSector['name']);
                        $sector->setProductManagementSectorId($platformSector['id']);
                        break;
                    }
                }

                $sectorAndCategoriesStatus = $this->sectorService->createSectorAndCategories($sector);

                if ($sectorAndCategoriesStatus) {
                    $this->addFlash('status', 'success');
                    $this->addFlash('message', 'İşlem Başarılı!');

                    return $this->redirectToRoute('admin.sectors.index');
                } else {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', 'İşlem Başarısız!');
                }
            } else {
                $form->get('categories')->addError(new FormError($translator->trans('system.sector.main_category.not_found')));
            }
        }

        return $this->render('admin/sectors/create.html.twig', [
            'form' => $form->createView(),
            'sector' => $sector,
            'platformSectors' => $platformSectors,
            'categories' => $sector instanceof Sector && !empty($sector->getProductManagementSectorId()) ?
                $pmSectorService->getCategories($sector->getProductManagementSectorId())['categories'] : [],
            'registeredSectors' => $this->sectorService->getAllUndeleted(),
        ]);
    }

    /**
     * @Route("/sectors/{sectorId}/get-categories", requirements={"sectorId"="\d+"}, methods={"GET"}, name="admin.sectors.get_categories")
     *
     * @param Request $request
     * @param int $sectorId
     * @param \App\Service\ProductManagement\SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getSectorCategories(int $sectorId, \App\Service\ProductManagement\SectorService $sectorService)
    {
        return $this->json([
            'success' => true,
            'categories' => $sectorService->getCategories((int)$sectorId)['categories'],
        ]);
    }
}
