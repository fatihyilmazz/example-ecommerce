<?php

namespace App\Controller\Admin;

use App\Utils\TaxUtil;
use App\Entity\Merchant;
use App\Service\CdnService;
use Psr\Log\LoggerInterface;
use App\Entity\MerchantHistory;
use App\Service\MerchantService;
use App\Service\ShowFileService;
use App\Form\MerchantHistoryType;
use App\Security\Voter\UserVoter;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Service\PaymentManagement\Request\Merchant\MerchantRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MarketPlaceController extends AbstractAdminController
{
    const MARKETPLACE_STATUS_ACTIVE = 1;
    const MARKETPLACE_STATUS_PASSIVE = 2;

    /**
     * @var MerchantService
     */
    protected $merchantService;

    /**
     * @var CdnService;
     */
    protected $cdnService;

    /**
     * @var ShowFileService
     */
    protected $showFileService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param MerchantService $merchantService
     * @param CdnService $cdnService
     * @param ShowFileService $showFileService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        MerchantService $merchantService,
        CdnService $cdnService,
        ShowFileService $showFileService,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->merchantService = $merchantService;
        $this->cdnService = $cdnService;
        $this->showFileService = $showFileService;
        $this->translator = $translator;
    }

    /**
     * @Route("marketplaces", methods={"GET"}, name="admin.marketplaces.index")
     *
     * @param Request $request
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER, UserVoter::ROLE_SALES_TEAM], $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $status = $request->query->getAlpha('status');

        if ($status == 'active') {
            $title = 'Aktif Pazaryerleri';
            $paginate = $this->merchantService->paginateMarketplaceMerchants($page, $limit, self::MARKETPLACE_STATUS_ACTIVE);
        } elseif ($status == 'passive') {
            $title = 'Kapalı Pazaryerleri';
            $paginate = $this->merchantService->paginateMarketplaceMerchants($page, $limit, self::MARKETPLACE_STATUS_PASSIVE);
        } elseif ($status == 'all') {
            $title = 'Tüm Pazaryerleri';
            $paginate = $this->merchantService->paginateMarketplaceMerchants($page, $limit);
        } else {
            $logger->error(sprintf('[MarketPlaceController][index] Invalid status. Status: %s', $status));

            throw $this->createNotFoundException();
        }

        return $this->render('admin/marketplaces/index.html.twig', [
            'title' => $title,
            'type' => 'list',
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchants' => (array)$paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("marketplaces/pending-list", methods={"GET"}, name="admin.marketplaces.pending_list")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPendingList(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER, UserVoter::ROLE_SALES_TEAM], $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->merchantService->paginateMarketplaceMerchantPendingList($page, $limit);

        return $this->render('admin/marketplaces/waitingApproved.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchantHistories' => (array)$paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("marketplaces/pending-list/{merchantHistoryId}/approve", methods={"GET", "POST"}, requirements={"merchantHistoryId"="\d+"}, name="admin.marketplaces.approve")
     *
     * @ParamConverter("merchantHistory", options={"mapping"={"merchantHistoryId"="id"}})
     *
     * @param Request $request
     * @param MerchantHistory $merchantHistory
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function approveMarketplaceRequest(Request $request, MerchantHistory $merchantHistory)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER, UserVoter::ROLE_SALES_TEAM], $this->getUser());

        $form = $this->createForm(MerchantHistoryType::class, $merchantHistory, [
            'action' => $this->generateUrl('admin.marketplaces.approve', ['merchantHistoryId' => $merchantHistory->getId()]),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $merchantHistory->setProcessedAt(new \DateTime());
            $merchantHistory->setProcessedBy($this->getUser());

            $createMerchantResult = null;

            if ($merchantHistory->getIsApproved()) {
                /** @var Merchant $merchant */
                $merchant = $merchantHistory->getMerchant();

                $merchantRequest = new MerchantRequest();

                $merchantRequest->setMerchantId($merchant->getId());
                $merchantRequest->setName($merchant->getShopName());
                $merchantRequest->setEmail($merchant->getEmail());
                $merchantRequest->setTaxOffice(TaxUtil::taxOfficeCharacterCompletion($merchant->getTaxOffice()));
                $merchantRequest->setTaxNumber($merchant->getTaxNumber());
                $merchantRequest->setLegalCompanyTitle($merchantHistory->getLegalCompanyTitle());
                $merchantRequest->setIbanName($merchantHistory->getIbanName());
                $merchantRequest->setIban(str_replace(' ', '', $merchantHistory->getIban()));
                $merchantRequest->setAddress($merchantHistory->getAddress());

                $createMerchantResult = $this->merchantService->createMerchant($merchantRequest);

                if ($createMerchantResult['success'] == true) {
                    $merchant->setIbanName($merchantHistory->getIbanName());
                    $merchant->setIban($merchantHistory->getIban());
                    $merchant->setMerchantKey($createMerchantResult['data']['merchantKey']);
                    $merchant->setLegalCompanyTitle($merchantHistory->getLegalCompanyTitle());
                    $merchant->setReturnAddress($merchantHistory->getAddress());
                    $merchant->setApprovedAt(new \DateTime());

                    $merchant = $this->merchantService->update($merchant);

                    if ($merchant instanceof Merchant) {
                        $merchantHistory = $this->merchantService->updateMerchantHistory($merchantHistory, \App\Entity\MerchantHistoryType::TYPE_CREATE_MARKETPLACE_REQUEST);

                        if ($merchantHistory instanceof MerchantHistory) {
                            $this->merchantService->sendNewSellerRequestProcessedMailToMerchant($merchantHistory);

                            $this->addFlash('status', 'success');
                            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                            return $this->redirectToRoute('admin.marketplaces.pending_list');
                        }
                    }
                }
            } else {
                $merchantHistory = $this->merchantService->updateMerchantHistory($merchantHistory, \App\Entity\MerchantHistoryType::TYPE_CREATE_MARKETPLACE_REQUEST);

                if ($merchantHistory instanceof MerchantHistory) {
                    $this->merchantService->sendNewSellerRequestProcessedMailToMerchant($merchantHistory);

                    $this->addFlash('status', 'success');
                    $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                    return $this->redirectToRoute('admin.marketplaces.pending_list');
                }
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', isset($createMerchantResult['message']) ? $createMerchantResult['message'] : $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/marketplaces/approve.html.twig', [
            'form' => $form->createView(),
            'merchantHistory' => $merchantHistory,
            'rejectReasons' => $this->merchantService->getMarketplaceRequestRejectReasons(),
        ]);
    }

    /**
     * @Route("marketplaces/rejected-list", methods={"GET"}, name="admin.marketplaces.rejected_list")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function marketplaceRequestRejectedList(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER, UserVoter::ROLE_SALES_TEAM], $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->merchantService->paginateMarketplaceRequestRejectedList($page, $limit);

        return $this->render('admin/marketplaces/rejectedList.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchantHistories' => (array)$paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("marketplaces/{merchant}/change-marketplace-status", methods={"GET", "PUT"}, name="admin.merchants.change_marketplace_status")
     *
     * @ParamConverter("merchant", options={"mapping"={"merchant"="id"}})
     *
     * @param Request $request
     * @param Merchant $merchant
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function changeStatusOfMerchantsMarketplace(Request $request, Merchant $merchant, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER, UserVoter::ROLE_SALES_TEAM], $this->getUser());

        if (empty($merchant->getMerchantKey())) {
            $logger->error(sprintf('[MarketPlaceController][changeStatusOfMerchantsMarketplace] Invalid merchant. MerchantId: %s', $merchant->getId()));

            throw $this->createNotFoundException();
        }

        $merchantHistory = new MerchantHistory();

        $form = $this->createForm(MerchantHistoryType::class, $merchantHistory, [
            'action' => $this->generateUrl('admin.merchants.change_marketplace_status', ['merchant' => $merchant->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($merchantHistory->getIsApproved()) {
                $merchant->setMarketplaceClosedAt(null);
            } else {
                $merchant->setMarketplaceClosedAt(new \DateTime());
            }

            $merchant = $this->merchantService->update($merchant);

            if ($merchant instanceof Merchant) {
                $merchantHistory->setMerchant($merchant);
                $merchantHistory->setProcessedBy($this->getUser());
                $merchantHistory->setProcessedAt(new \DateTime());

                $merchantHistory = $this->merchantService->updateMerchantHistory($merchantHistory, \App\Entity\MerchantHistoryType::TYPE_MARKETPLACE_STATUS_CHANGE);

                if ($merchantHistory instanceof MerchantHistory) {
                    $this->merchantService->sendMarketPlaceStatusChangedMailToMerchant($merchantHistory);

                    $this->addFlash('status', 'success');
                    $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                    if ($merchantHistory->getIsApproved()) {
                        return $this->redirectToRoute('admin.marketplaces.index', ['status' => 'active']);
                    }

                    return $this->redirectToRoute('admin.marketplaces.index', ['status' => 'passive']);
                }
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/marketplaces/changeMarketplaceStatus.html.twig', [
            'form' => $form->createView(),
            'merchant' => $merchant,
            'rejectReasons' => $this->merchantService->getMarketplaceRequestRejectReasons(),
        ]);
    }
}
