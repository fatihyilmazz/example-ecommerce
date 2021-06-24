<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Currency;
use App\Entity\Merchant;
use App\Utils\MoneyUtil;
use App\Form\AddressType;
use App\Form\MerchantType;
use App\Service\CdnService;
use Psr\Log\LoggerInterface;
use App\Service\UserService;
use App\Service\OrderService;
use App\Entity\MerchantSector;
use App\Service\SectorService;
use App\Service\AddressService;
use App\Service\ProductService;
use App\Service\SegmentService;
use App\Service\CategoryService;
use App\Service\MerchantService;
use App\Service\ShowFileService;
use App\Form\MerchantApproveType;
use App\Service\UserAgentService;
use App\Security\Voter\UserVoter;
use App\Service\AddressTypeService;
use App\Entity\MerchantSectorStatus;
use App\Entity\MerchantSectorHistory;
use JMS\Serializer\SerializerInterface;
use App\Form\MerchantSectorHistoryType;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Entity\MerchantSectorHistoryStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use App\Form\MerchantBankAccountInformationType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Service\ProductManagement\Request\Product\ProductFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class MerchantController extends AbstractAdminController
{
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
     * @var UserAgentService
     */
    protected $userAgentService;

    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param MerchantService $merchantService
     * @param CdnService $cdnService
     * @param ShowFileService $showFileService
     * @param TranslatorInterface $translator
     * @param UserAgentService $userAgentService
     * @param SectorService $sectorService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        MerchantService $merchantService,
        CdnService $cdnService,
        ShowFileService $showFileService,
        TranslatorInterface $translator,
        UserAgentService $userAgentService,
        SectorService $sectorService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->merchantService = $merchantService;
        $this->cdnService = $cdnService;
        $this->showFileService = $showFileService;
        $this->translator = $translator;
        $this->userAgentService = $userAgentService;
        $this->sectorService = $sectorService;
    }

    /**
     * @Route("/merchants", methods={"GET"}, name="admin.merchants.index")
     *
     * @param Request $request
     * @param SegmentService $segmentService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, SegmentService $segmentService)
    {
        $merchantFilter = $this->merchantService->prepareMerchantFilterWithRequest($request);

        $merchantPaginate = $this->merchantService->getMerchantsWithFilter($merchantFilter);

        return $this->render('admin/merchants/index.html.twig', [
            'currentPage' => $merchantPaginate->getCurrentPage(),
            'totalPage' => $merchantPaginate->getNbPages(),
            'totalRecord' => $merchantPaginate->getNbResults(),
            'merchants' => (array) $merchantPaginate->getCurrentPageResults(),
            'merchantFilter' => $merchantFilter,
            'currentGroups' => $this->merchantService->getCurrentGroupCodes(),
            'segments' => $segmentService->getAll(),
            'sectors' => $this->sectorService->getAll()
        ]);
    }

    /**
     * @Route("/merchants/export/excel", methods={"GET"}, name="admin.merchants.export.excel")
     *
     * @param Request $request
     *
     * @return BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createExcelFileForAllMerchants(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $merchantFilter = $this->merchantService->prepareMerchantFilterWithRequest($request);
        $merchantFilter->setPaginate(false);

        $merchants = $this->merchantService->getMerchantsWithFilter($merchantFilter);

        $fileName = 'Tüm Bayiler';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fileName);

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'ID');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Bayi Adı');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Yetkili');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Cari Kodu');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Durum');
        $spreadsheet->getActiveSheet()->setCellValue('F1', 'Pazaryeri Durumu');
        $spreadsheet->getActiveSheet()->setCellValue('G1', 'Kayıt Tarihi');

        /** @var Merchant $merchant */
        foreach ($merchants as $index => $merchant) {
            $index += 2;

            foreach ($merchant->getUsers() as $user) {
                if (in_array(UserVoter::ROLE_OWNER, $user->getMerchantRoles())) {
                    break;
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'A', $index), $merchant->getId());
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'B', $index), $merchant->getShopName() ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'C', $index), sprintf('%s %s', $user->getFirstName(), $user->getLastName()) ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'D', $index), $merchant->getCurrentCode() ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'E', $index), $merchant->isActive() ? 'Aktif' : 'Pasif');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'F', $index), $merchant->getMarketplaceClosedAt() ? 'Aktif' : 'Pasif');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'G', $index), $merchant->getCreatedAt());
        }

        $writer = new Xls($spreadsheet);

        $fileName = sprintf('%s.xls', $fileName);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/merchants/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.merchants.edit")
     *
     * @param Request $request
     * @param Merchant $merchant
     * @param UserService $userService
     * @param SegmentService $segmentService
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function edit(Request $request, Merchant $merchant, UserService $userService, SegmentService $segmentService, SectorService $sectorService)
    {
        $merchantId = $merchant->getId();

        $form = $this->createForm(MerchantType::class, $merchant, [
            'action' => $this->generateUrl('admin.merchants.edit', ['id' => $merchantId]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);
        if (!empty($merchant->getSegmentId())) {
            $segment = $segmentService->getSegmentById($merchant->getSegmentId());
        }

        if (!empty($merchant->getCurrentGroupId())) {
            $currentGroup = $this->merchantService->getCurrentGroupById($merchant->getCurrentGroupId());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

            if (($request->files->get('logoUrl') instanceof File)) {
                $merchant = $this->cdnService->uploadImage($request->files->get('logoUrl'), $merchant, "setLogoUrl");

                if (!($merchant instanceof Merchant)) {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));

                    return $this->redirectToRoute('admin.merchants.edit', ['id' => $merchantId]);
                }
            }

            if (($request->files->get('bannerUrl') instanceof File)) {
                $merchant = $this->cdnService->uploadImage($request->files->get('bannerUrl'), $merchant, "setBannerUrl");

                if (!($merchant instanceof Merchant)) {
                    $this->addFlash('status', 'error');
                    $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));

                    return $this->redirectToRoute('admin.merchants.edit', ['id' => $merchantId]);
                }
            }

            if ($request->request->getBoolean('approved')) {
                $merchant->setVerifiedAt(new \DateTime());
            }

            $user = $userService->getOwnerUserToMerchant($merchant);

            $merchantUpdate = $this->merchantService->update($merchant, $user ?? null, $request->request->getBoolean('approved'));
            if ($merchantUpdate instanceof Merchant) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/merchants/edit.html.twig', [
            'form' => $form->createView(),
            'merchant' => $merchantUpdate ?? $merchant,
            'merchantAddresses' => $this->merchantService->getMerchantAddresses($merchant),
            'segments' => $segmentService->getAll(),
            'currentGroups' => $this->merchantService->getCurrentGroupCodes(),
            'segment' => $segment ?? [],
            'currentGroup' => $currentGroup ?? [],
            'sectors' => $sectorService->getAll(),
            'userAgents' => $this->userAgentService->getAllUserAgents() ?? []
        ]);
    }

    /**
     * @Route("/merchants/{id}/bank-account-information", requirements={"id"="\d+"}, methods={"PUT"}, name="admin.merchants.edit-bank-account-information")
     *
     * @param Request $request
     * @param Merchant $merchant
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function editMerchantBankAccountInformation(Request $request, Merchant $merchant)
    {
        $merchantId = $merchant->getId();

        $form = $this->createForm(MerchantBankAccountInformationType::class, $merchant, [
            'action' => $this->generateUrl('admin.merchants.edit-bank-account-information', ['id' => $merchantId]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $merchantUpdate = $this->merchantService->updateMerchantBankAccountInformation($merchant);

            if ($merchantUpdate instanceof Merchant) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.index');
            }
        }

        // @TODO show form errors
        $this->addFlash('status', 'error');
        $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));

        return $this->redirectToRoute('admin.merchants.edit', [
            'id' => $merchantId
        ]);
    }

    /**
     * @Route("/merchants/files/{fileType}/{fileName}",
     *     requirements={"fileType"="\d+","fileName"="(contract|signature|tax|journal)-[a-zA-Z\-0-9]*.[a-zA-Z]*"},
     *     methods={"GET"},
     *     name="admin.merchants.file.show")
     *
     * @param int $fileType
     * @param string $fileName
     *
     * @return bool|BinaryFileResponse|\Symfony\Component\HttpFoundation\Response|null
     */
    public function showFile(int $fileType, string $fileName)
    {
        /** @var BinaryFileResponse */
        $fileResponse = $this->showFileService->showFile($fileType, $fileName);

        if (!($fileResponse instanceof  BinaryFileResponse)) {
            return $this->render('admin/errors/error404.html.twig');
        }

        return $fileResponse;
    }

    /**
     * @Route("/merchants/{merchantId}/addresses/{addressId}",
     *     requirements={"merchantId"="\d+", "addressId"="\d+"},
     *     methods={"GET", "PUT"},
     *     name="admin.merchants.address.edit")
     *
     * @ParamConverter("address", options={"mapping"={"addressId"="id"}})
     * @ParamConverter("merchant", options={"mapping"={"merchantId"="id"}})
     *
     * @param Request $request
     * @param Address $address
     * @param Merchant $merchant
     * @param AddressService $addressService
     * @param AddressTypeService $addressTypeService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editMerchantAddress(
        Request $request,
        Address $address,
        Merchant $merchant,
        AddressService $addressService,
        AddressTypeService $addressTypeService
    ) {
        $form = $this->createForm(AddressType::class, $address, [
            'action' => $this->generateUrl('admin.merchants.address.edit', [
                'merchantId' => $merchant->getId(),
                'addressId' => $address->getId()
            ]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

            $address = $addressService->update($address);

            if ($address instanceof Address) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.edit', ['id' => $merchant->getId()]);
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/merchants/addresses/edit.html.twig', [
            'form' => $form->createView(),
            'address' => $address,
            'cities' => $addressService->getAllCities(),
            'addressTypes' => $addressTypeService->getAll(),
            'counties' => $addressService->getCountyOfCity($address->getCity()),
        ]);
    }

    /**
     * @Route("/merchants/reports", methods={"GET"}, name="admin.merchants.reports")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function reports(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $sellerMerchantFilter = $this->merchantService->prepareSellerMerchantFilterWithRequest($request);

        $result = $this->merchantService->getSellerMerchantNumberOfOrderAndProducts($sellerMerchantFilter);

        return $this->render('admin/merchants/reports/index.html.twig', [
            'currentPage' => $result['pagination']['currentPage'],
            'totalPage' => $result['pagination']['totalPage'],
            'totalRecord' => $result['pagination']['totalRecord'],
            'merchantsAndNumberOfOrdersAndProducts' => $result['merchantsAndNumberOfOrdersAndProducts'],
            'sellerMerchantFilter' => $sellerMerchantFilter,
            'sellerMerchants' => $this->merchantService->getSellerMerchants(),
            'sectors' => $this->sectorService->getAll(),
        ]);
    }

    /**
     * @Route("/merchants/reports/export/excel", methods={"GET"}, name="admin.merchants.reports.export_excel")
     *
     * @param Request $request
     *
     * @return BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createExcelFileForMerchantReports(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $sellerMerchantFilter = $this->merchantService->prepareSellerMerchantFilterWithRequest($request);
        $sellerMerchantFilter->setPaginate(false);

        $result = $this->merchantService->getSellerMerchantNumberOfOrderAndProducts($sellerMerchantFilter);

        $title = 'Bayi Raporları Listesi';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'ID');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Satış Yapan Bayi');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Bayi Durumu');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Pazaryeri Durumu');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Aktif Ürün Sayısı (Aktif,Pasif,Bekleyen,Reddedilen Dahil)');
        $spreadsheet->getActiveSheet()->setCellValue('F1', 'Sipariş Sayısı');

        foreach ($result['merchantsAndNumberOfOrdersAndProducts'] as $index => $merchantAndNumberOfOrdersAndProducts) {
            $index += 2;

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'A', $index), $merchantAndNumberOfOrdersAndProducts['id']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'B', $index), $merchantAndNumberOfOrdersAndProducts['shop_name']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'C', $index), ($merchantAndNumberOfOrdersAndProducts['is_active'] ? 'Aktif' : 'Pasif'));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'D', $index), ($merchantAndNumberOfOrdersAndProducts['marketplace_closed_at'] ? 'Aktif' : 'Pasif'));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'E', $index), $merchantAndNumberOfOrdersAndProducts['number_of_product']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'F', $index), $merchantAndNumberOfOrdersAndProducts['number_of_order']);
        }

        $writer = new Xls($spreadsheet);

        $fileName = sprintf('%s.xls', $title);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/merchants/reports/orders", methods={"GET"}, name="admin.merchants.orders")
     *
     * @param Request $request
     * @param OrderService $orderService
     * @param UserService $userService
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orders(Request $request, OrderService $orderService, UserService $userService, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $merchantId = $request->get('merchantId', null);

        $merchantIdAndNameArray = $this->merchantService->getMerchantIdAndNameById($merchantId);

        if (empty($merchantIdAndNameArray)) {
            $logger->error(sprintf('[MerchantController][orders] Merchant not found. MerchantId: %s', $merchantId));

            throw $this->createNotFoundException();
        }

        $ordersOnlyHasMerchantsProductsFilter = $orderService->prepareOrdersOnlyHasMerchantsProductsFilterWithRequest($request);
        $ordersOnlyHasMerchantsProductsFilter->setSellerMerchant($merchantIdAndNameArray);

        $orderPaginate = $orderService->getOrdersOnlyHasMerchantsProductsWithFilter($ordersOnlyHasMerchantsProductsFilter);

        return $this->render('admin/merchants/reports/orders.html.twig', [
            'orders' => (array) $orderPaginate->getCurrentPageResults(),
            'currentPage' => $orderPaginate->getCurrentPage(),
            'totalPage' => $orderPaginate->getNbPages(),
            'totalRecord' => $orderPaginate->getNbResults(),
            'ordersOnlyHasMerchantsProductsFilter' => $ordersOnlyHasMerchantsProductsFilter,
            'merchants' => $this->merchantService->getMerchantsIdAndName(),
            'users' => $userService->getUsersIdAndName(),
        ]);
    }

    /**
     * @Route("/merchants/{merchantId}/reports/orders/export/excel", methods={"GET"}, name="admin.merchants.reports.orders.export_excel")
     *
     * @param Request $request
     * @param OrderService $orderService
     * @param LoggerInterface $logger
     *
     * @return BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createExcelFile(Request $request, OrderService $orderService, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $merchantId = $request->get('merchantId', null);

        $merchantIdAndNameArray = $this->merchantService->getMerchantIdAndNameById($merchantId);

        if (empty($merchantIdAndNameArray)) {
            $logger->error(sprintf('[MerchantController][createExcelFile] Merchant not found. MerchantId: %s', $merchantId));

            throw $this->createNotFoundException();
        }

        $ordersOnlyHasMerchantsProductsFilter = $orderService->prepareOrdersOnlyHasMerchantsProductsFilterWithRequest($request);
        $ordersOnlyHasMerchantsProductsFilter->setSellerMerchant($merchantIdAndNameArray);
        $ordersOnlyHasMerchantsProductsFilter->setPaginate(false);

        $orders = $orderService->getOrdersOnlyHasMerchantsProductsWithFilter($ordersOnlyHasMerchantsProductsFilter);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(sprintf('%s Ürün Satış Listesi', $ordersOnlyHasMerchantsProductsFilter->getSellerMerchant()['shopName']));

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Sipariş No');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Satın Alan Bayi');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Satın Alan Kullanıcı');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Bayinin Satılan Ürün Sayısı');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Sipariş Tutarı (KDV Dahil)');
        $spreadsheet->getActiveSheet()->setCellValue('F1', 'Ürünler Tutarı (KDV Dahil)');
        $spreadsheet->getActiveSheet()->setCellValue('G1', 'Sipariş Tarihi');


        /** @var Order $order */
        foreach ($orders as $index => $order) {
            $index += 2;

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'A', $index), $order->getId());
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'B', $index), $order->getMerchant()->getShopName());
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'C', $index), sprintf('%s %s', $order->getUser()->getFirstName(), $order->getUser()->getLastName()));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'D', $index), $order->getOrderProducts()->count());
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'E', $index), sprintf('%s %s', MoneyUtil::displayFormatTL($order->getTotalPrice()), $order->getCurrency()->getName()));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'F', $index), sprintf('%s %s', MoneyUtil::displayFormatTL($order->getSummary()->getGrandTotalTL()), Currency::CODE_TL));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'G', $index), $order->getCreatedAt());
        }

        $writer = new Xls($spreadsheet);

        $fileName = sprintf('%s Ürün Satış Listesi.xls', $ordersOnlyHasMerchantsProductsFilter->getSellerMerchant()['shopName']);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/merchants/reports/products", requirements={"id"="\d+"}, methods={"GET"}, name="admin.merchants.products")
     *
     * @param Request $request
     * @param ProductService $productService
     * @param LoggerInterface $logger
     * @param CategoryService $categoryService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function products(Request $request, ProductService $productService, LoggerInterface $logger, CategoryService $categoryService)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $merchantId = $request->get('merchantId', null);

        if (empty($merchantId)) {
            throw $this->createNotFoundException();
        }

        $merchant = $this->merchantService->getMerchantById($merchantId);

        if (!($merchant instanceof Merchant)) {
            $logger->error(sprintf('[MerchantController][products] Merchant not found. MerchantId: %s', $merchantId));

            throw $this->createNotFoundException();
        }

        $productFilter = $this->merchantService->prepareProductFilter($request, $merchant);

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['brand', 'medias', 'platforms', 'segmentPrices', 'campaigns']);

        $products = $productService->search($productSearchRequest);

        return $this->render('admin/merchants/reports/products.html.twig', [
            'merchant' => $merchant,
            'products' => $products['products'],
            'currentPage' => $products['current_page'],
            'totalPage' => $products['total_page'],
            'totalRecord' => $products['total_record'],
            'sectors' => $this->sectorService->getAll(),
            'categories' => $categoryService->getAllActiveCategories(),
            'productFilter' => $productFilter,
        ]);
    }

    /**
     * @Route("/merchants/reports/products/export/excell", methods={"GET"}, name="admin.merchants.products.export_excel")
     *
     * @param Request $request
     * @param ProductService $productService
     * @param LoggerInterface $logger
     *
     * @return BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createExcelFileForMerchantProducts(Request $request, ProductService $productService, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        $merchantId = $request->get('merchantId', null);

        if (empty($merchantId)) {
            throw $this->createNotFoundException();
        }

        $merchant = $this->merchantService->getMerchantById($merchantId);

        if (!($merchant instanceof Merchant)) {
            $logger->error(sprintf('[MerchantController][createExcelFileForMerchantProducts] Merchant not found. MerchantId: %s', $merchantId));

            throw $this->createNotFoundException();
        }

        $productFilter = new ProductFilter();
        $productFilter->setSegmentId($merchant->getSegmentId());
        $productFilter->setCurrentGroupId($merchant->getCurrentGroupId());
        $productFilter->setMerchantId($merchant->getId());
        $productFilter->setProductPlatformStatusId(null);
        $productFilter->setProductPlatformStatusIds([
            ProductFilter::PRODUCT_PLATFORM_STATUS_ID_ACTIVE,
            ProductFilter::PRODUCT_PLATFORM_STATUS_ID_PASSIVE,
            ProductFilter::PRODUCT_PLATFORM_STATUS_ID_PENDING,
            ProductFilter::PRODUCT_PLATFORM_STATUS_ID_DECLINE,
        ]);
        $productFilter->setOnlyActiveProducts(false);
        $productFilter->setPaginate(false);

        foreach ($merchant->getMerchantSectors() as $merchantSector) {
            if (empty($merchantSector->getDeletedAt()) && $merchantSector->getMerchantSectorStatus()->getId() == MerchantSectorStatus::STATUS_TYPE_ACTIVE_ID) {
                $productFilter->setSectorId($sectorId = $merchantSector->getSector()->getProductManagementSectorId());
                break;
            }
        }

        if ($page = $request->query->getInt('page', 1)) {
            $productFilter->setPage($page);
        }

        $productSearchRequest = new ProductSearchRequest();
        $productSearchRequest->setFilter($productFilter);
        $productSearchRequest->setIncludes(['brand', 'medias', 'platforms', 'segmentPrices', 'campaigns']);

        $products = $productService->search($productSearchRequest);

        $sectorIds = new ArrayCollection();
        foreach ($products['products'] as $product) {
            if (!$sectorIds->contains($product['sectorId'])) {
                $sectorIds->add($product['sectorId']);
            }
        }

        $sectors = $this->sectorService->getSectorNameListByPmSectorIds($sectorIds->toArray());

        $sectorName = '';
        if (($sectors instanceof ArrayCollection) && ($sectors->count() == 1)) {
            $sectorName = $sectors->first()['name'];
        }

        $title = sprintf('%s Ürün Listesi', substr($merchant->getShopName(), 0, 18));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Ürün No');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Stok Kodu');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Ürün Adı');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Sektör');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Marka');
        $spreadsheet->getActiveSheet()->setCellValue('F1', 'Kategori');
        $spreadsheet->getActiveSheet()->setCellValue('G1', 'Satış Fiyatı');
        $spreadsheet->getActiveSheet()->setCellValue('H1', 'Stok');
        $spreadsheet->getActiveSheet()->setCellValue('I1', 'Durum');
        $spreadsheet->getActiveSheet()->setCellValue('J1', 'Tarih');

        foreach ($products['products'] as $index => $product) {
            $index += 2;

            if (empty($sectorName) && ($sectors instanceof ArrayCollection)) {
                $sector = $sectors->filter(function ($sector) use ($product) {
                    if ($sector['productManagementSectorId'] == $product['sectorId']) {
                        return $sector;
                    }
                })->toArray();

                $sectorName = $sector[0]['name'];
            }

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'A', $index), $product['id']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'B', $index), $product['sku']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'C', $index), $product['name']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'D', $index), $sectorName);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'E', $index), $product['brand']['name']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'F', $index), $product['category']['name']);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'G', $index), sprintf('%s %s', MoneyUtil::formatPrice($product['price']), $product['currency']));
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'H', $index), $product['quantity']);

            if ($product['is_active'] == true) {
                if ($product['product_platforms'][0]['product_platform_status_id'] == ProductFilter::PRODUCT_PLATFORM_STATUS_ID_ACTIVE) {
                    $status = 'Aktif';
                } elseif ($product['product_platforms'][0]['product_platform_status_id'] == ProductFilter::PRODUCT_PLATFORM_STATUS_ID_PASSIVE) {
                    $status = 'Pasif';
                } elseif ($product['product_platforms'][0]['product_platform_status_id'] == ProductFilter::PRODUCT_PLATFORM_STATUS_ID_PENDING) {
                    $status = 'Beklemede';
                } elseif ($product['product_platforms'][0]['product_platform_status_id'] == ProductFilter::PRODUCT_PLATFORM_STATUS_ID_DECLINE) {
                    $status = 'Reddedildi';
                }
            } else {
                $status = 'Yayından Kaldırıldı';
            }

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'I', $index), $status);
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'J', $index), $product['createdAt']);
        }

        $writer = new Xls($spreadsheet);

        $fileName = sprintf('%s.xls', $title);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/merchants/pending-approval", methods={"GET"}, name="admin.merchants.pending-approval")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pendingApproval(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->merchantService->getPendingMerchantsForApproval($page, $limit);

        return $this->render('admin/merchants/pendingApproval.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchants' => (array) $paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("/merchants/pending-approval/export/excel", methods={"GET"}, name="admin.merchants.pending-approval.export.excel")
     *
     * @return BinaryFileResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createExcelFileForPendingApprovalMerchantList()
    {
        $pendingMerchants = $this->merchantService->getPendingMerchantsForApproval();

        $fileName = 'Onay Bekleyen Bayiler Listesi';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fileName);

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'ID');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Bayi Adı');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Yetkili');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Cari Kodu');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Kayıt Tarihi');

        /** @var Merchant $merchant */
        foreach ($pendingMerchants as $index => $merchant) {
            $index += 2;

            foreach ($merchant->getUsers() as $user) {
                if (in_array(UserVoter::ROLE_OWNER, $user->getMerchantRoles())) {
                    break;
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'A', $index), $merchant->getId());
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'B', $index), $merchant->getShopName() ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'C', $index), sprintf('%s %s', $user->getFirstName(), $user->getLastName()) ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'D', $index), $merchant->getCurrentCode() ?? '-');
            $spreadsheet->getActiveSheet()->setCellValue(sprintf('%s%s', 'E', $index), $merchant->getCreatedAt());
        }

        $writer = new Xls($spreadsheet);

        $fileName = sprintf('%s.xls', $fileName);

        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/merchants/approve/{id}", methods={"GET", "POST"}, name="admin.merchants.approve")
     *
     * @ParamConverter("merchants", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Merchant $merchant
     * @param SegmentService $segmentService
     * @param UserService $userService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function merchantApprove(Request $request, Merchant $merchant, SegmentService $segmentService, UserService $userService, SectorService $sectorService)
    {
        $form = $this->createForm(MerchantApproveType::class, $merchant, [
            'action' => $this->generateUrl('admin.merchants.approve', [
                'id' => $merchant->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        $merchantOwnerUser = $userService->getOwnerUserToMerchant($merchant);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

            $merchant->setVerifiedAt(new \DateTime());
            $merchant->setIsActive(true);

            $merchant = $this->merchantService->update($merchant, $merchantOwnerUser, true);

            if ($merchant instanceof Merchant) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.pending-approval');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/merchants/approve.html.twig', [
            'form' => $form->createView(),
            'segments' => $segmentService->getAll(),
            'currentGroups' => $this->merchantService->getCurrentGroupCodes(true),
            'merchantOwnerUser' => $merchantOwnerUser,
            'sectors' => $sectorService->getAll(),
            'userAgents' => $this->userAgentService->getAllUserAgents() ?? []
        ]);
    }

    /**
     * @Route("merchants/sectors/{status}", methods={"GET"}, requirements={"status":"pending|rejected|all"}, name="admin.merchants.sectors.index")
     *
     * @param Request $request
     * @param string $status
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function merchantSectorApplications(Request $request, string $status)
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $merchantSectorHistoryStatusId = null;

        switch ($status) {
            case 'pending':
                $merchantSectorHistoryStatusId = MerchantSectorHistoryStatus::ID_STATUS_PENDING;
                $pageTitle = 'Onay Bekleyen';
                break;
            case 'rejected':
                $merchantSectorHistoryStatusId = MerchantSectorHistoryStatus::ID_STATUS_REJECTED;
                $pageTitle = 'Reddedilen';
                break;
            case 'all':
                $pageTitle = 'Tüm';
                break;
            default:
                throw $this->createNotFoundException();
        }

        $paginate = $this->merchantService->paginateMerchantSectorApplications($page, $limit, $merchantSectorHistoryStatusId);

        return $this->render('admin/sector_requests/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchantSectorApplications' => (array)$paginate->getCurrentPageResults(),
            'pageTitle' => $pageTitle,
        ]);
    }

    /**
     * @Route("merchants/sectors/applications/{merchantSectorHistoryId}", requirements={"merchantSectorId"="\d+"}, methods={"GET", "POST"}, name="admin.merchants.sectors.application_detail")
     *
     * @Entity("merchantSectorHistory", expr="repository.find(merchantSectorHistoryId)")
     *
     * @param Request $request
     * @param MerchantSectorHistory $merchantSectorHistory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function merchantSectorApplicationDetail(Request $request, MerchantSectorHistory $merchantSectorHistory)
    {
        $form = $this->createForm(MerchantSectorHistoryType::class, $merchantSectorHistory, [
            'action' => $this->generateUrl('admin.merchants.sectors.application_detail', [
                'merchantSectorHistoryId' => $merchantSectorHistory->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $merchantSectorHistory->setProcessedBy($this->getUser());

            $merchantSectorRequest = $this->merchantService->updateMerchantSectorByHistory($merchantSectorHistory);

            if ($merchantSectorRequest instanceof MerchantSectorHistory) {
                $this->merchantService->sendMerchantSectorStatusChangedMailToMerchant($merchantSectorHistory);

                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.sectors.index', [
                    'status' => 'all'
                ]);
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sector_requests/detail.html.twig', [
            'form' => $form->createView(),
            'merchantSectorHistory' => $merchantSectorHistory,
            'rejectReasons' => $this->merchantService->getMerchantSectorRequestRejectReasons(),
            'pageTitle' => 'Bayi Sektör Onayı',
        ]);
    }

    /**
     * @Route("merchants/sectors/{merchantSectorId}", requirements={"merchantSectorId"="\d+"}, methods={"GET", "POST"}, name="admin.merchants.sectors.detail")
     *
     * @Entity("merchantSector", expr="repository.find(merchantSectorId)")
     *
     * @param Request $request
     * @param MerchantSector $merchantSector
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function merchantSectorDetail(Request $request, MerchantSector $merchantSector)
    {
        $merchantSectorHistory = new MerchantSectorHistory();

        $merchantSectorHistory->setMerchant($merchantSector->getMerchant());
        $merchantSectorHistory->setSector($merchantSector->getSector());

        $form = $this->createForm(MerchantSectorHistoryType::class, $merchantSectorHistory, [
            'action' => $this->generateUrl('admin.merchants.sectors.detail', [
                'merchantSectorId' => $merchantSector->getId(),
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $merchantSectorHistory->setProcessedBy($this->getUser());

            $merchantSectorRequest = $this->merchantService->updateMerchantSector($merchantSector, $merchantSectorHistory, $this->getUser());

            if ($merchantSectorRequest instanceof MerchantSectorHistory) {
                $this->merchantService->sendMerchantSectorStatusChangedMailToMerchant($merchantSectorHistory);

                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.merchants.sectors.closed_list');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sector_requests/detail.html.twig', [
            'form' => $form->createView(),
            'merchantSectorHistory' => $merchantSectorHistory,
            'rejectReasons' => $this->merchantService->getMerchantSectorRequestRejectReasons(),
            'pageTitle' => 'Bayi Sektör Düzenle',
        ]);
    }

    /**
     * @Route("merchants/sectors/closed-list", methods={"GET"}, name="admin.merchants.sectors.closed_list")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getClosedMerchantSectors(Request $request)
    {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_MANAGER], $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->merchantService->paginateClosedMerchantSectors($page, $limit);

        return $this->render('admin/sector_requests/closedList.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'merchantSectors' => (array)$paginate->getCurrentPageResults(),
        ]);
    }
}
