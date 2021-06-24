<?php

namespace App\Controller\Admin;

use App\Entity\Merchant;
use App\Entity\OrderStatus;
use App\Service\ReportService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DashboardController extends AbstractAdminController
{
    /**
     * @var ReportService
     */
    protected $reportService;

    /**
     * DashboardController constructor.
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param ReportService $reportService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        ReportService $reportService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->reportService = $reportService;
    }
    /**
     * @Route("/", name="admin.dashboard.index")
     */
    public function index()
    {
        $lastDays = ReportService::numberOfDailyLastOrders;

        $days = [];
        for ($subDay = 0; $subDay < $lastDays; $subDay++) {
            $now = new \DateTime();

            array_push($days, $now->sub(new \DateInterval("P{$subDay}D"))->format('d-m-Y'));
        }

        $excludedMerchantIds = [Merchant::ID_BIRCOM];

        return $this->render('admin/index.html.twig', [
            'days' => $days,
            'numberOfMerchants' => $this->reportService->getMerchantNumber(false),
            'numberOfActiveMerchants' => $this->reportService->getMerchantNumber(true),
            'numberOfSellerMerchants' => $this->reportService->getSellerMerchantNumber(true),
            'numberOfOrders' => $this->reportService->getOrderNumber([OrderStatus::ID_APPROVED, OrderStatus::ID_COMPLETED]),
            'dailyOrders' => $this->reportService->getDailyLastOrdersNumber($lastDays),
            'numberOfMonthlyOrdersWithBircom' => $this->reportService->getMonthlyLastOrdersNumber(),
            'numberOfMonthlyOrdersWithoutBircom' => $this->reportService->getMonthlyLastOrdersNumber($excludedMerchantIds),
            'amountOfMonthlyOrdersWithBircom' => $this->reportService->getMonthlyOrdersAmount(),
            'amountOfMonthlyOrdersWithoutBircom' => $this->reportService->getMonthlyOrdersAmount($excludedMerchantIds),
            'numberOfMarketPlaceStatus' => $this->reportService->getMarketPlaceStatuesNumber(),
            'numberOfMonthlyNewProductWithBircom' => $this->reportService->getMonthlyNewProductNumber(),
            'numberOfMonthlyNewProductWithoutBircom' => $this->reportService->getMonthlyNewProductNumber($excludedMerchantIds),
            'numberOfProductsSoldWithBircom' => $this->reportService->getMonthlyProductsSoldNumber(),
            'numberOfProductsSoldWithoutBircom' => $this->reportService->getMonthlyProductsSoldNumber($excludedMerchantIds),
        ]);
    }
}
