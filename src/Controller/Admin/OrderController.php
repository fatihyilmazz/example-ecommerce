<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\DTO\OrderSummaryDTO;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use App\Service\OrderService;
use App\Service\SectorService;
use App\Service\NetsisService;
use App\Service\MerchantService;
use App\Service\ShowFileService;
use App\Security\Voter\UserVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\PaymentManagement\PaymentService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class OrderController extends AbstractController
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ShowFileService
     */
    protected $showFileService;

    /**
     * @param OrderService $orderService
     * @param ShowFileService $showFileService
     */
    public function __construct(
        OrderService $orderService,
        ShowFileService $showFileService
    ) {
        $this->orderService = $orderService;
        $this->showFileService = $showFileService;
    }

    /**
     * @Route("/orders", methods={"GET"}, name="admin.orders.index")
     *
     * @param Request $request
     * @param MerchantService $merchantService
     * @param UserService $userService
     * @param PaymentService $paymentService
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, MerchantService $merchantService, UserService $userService, PaymentService $paymentService, SectorService $sectorService)
    {
        $orderFilter = $this->orderService->prepareOrderFilterWithRequest($request);
        $orderFilter->setPaginate(true);
        $orderFilter->setOrderBy('id');
        $orderFilter->setSortBy('DESC');
        $orderFilter->setAddSummary(true);

        $ordersPaginate = $this->orderService->getOrdersWithFilter($orderFilter);

        $orders = new ArrayCollection((array)$ordersPaginate->getCurrentPageResults());

        $orderIdsPaidByCreditCard = $orders->map(function (Order $order) {
            if ($order->getPaymentMethodId() == PaymentService::PAYMENT_METHOD_CARD) {
                return $order->getId();
            }
            return;
        })->toArray();

        $orderIdsPaidByCreditCard = array_filter($orderIdsPaidByCreditCard);

        if (!empty($orderIdsPaidByCreditCard)) {
            $orderPaymentDetailsForPaidByCreditCard = $paymentService->getTransactionInfoByReferenceIds(
                PaymentService::TRANSACTION_TYPE_ORDER_PAYMENT,
                $orderIdsPaidByCreditCard
            );

            foreach ((array) $ordersPaginate->getCurrentPageResults() as $order) {
                $orderSummaryDTO = new OrderSummaryDTO();

                foreach ($orderPaymentDetailsForPaidByCreditCard as $cardPaymentDetails) {
                    if ($order->getId() == $cardPaymentDetails['platform_reference_id']) {
                        $orderSummaryDTO->setCardPaymentDetails($cardPaymentDetails);
                        $order->setSummary($orderSummaryDTO);
                    }
                }
            }
        }

        return $this->render('admin/orders/index.html.twig', [
            'currentPage' => $ordersPaginate->getCurrentPage(),
            'totalPage' => $ordersPaginate->getNbPages(),
            'totalRecord' => $ordersPaginate->getNbResults(),
            'orders' => (array)$ordersPaginate->getCurrentPageResults(),
            'merchants' => $merchantService->getAll(),
            'users' => $userService->getAllUsersWithDeletedAndPassive(),
            'orderFilter' => $orderFilter,
            'sectors' => $sectorService->getAll()
        ]);
    }

    /**
     * @Route("/orders/{id}", requirements={"id"="\d+"}, methods={"GET"}, name="admin.orders.show")
     *
     * @ParamConverter("order", options={"mapping"={"id"="id"}})
     *
     * @param Order $order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Order $order)
    {
        $order = $this->orderService->prepareOrderSummary($order);

        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/orders/{id}/files/receipt/{fileName}",
     *     requirements={"id"="\d+","fileName"="[a-zA-Z\-0-9]*.[a-zA-Z]*"},
     *     methods={"GET"},
     *     name="admin.orders.file.show")
     *
     * @param string $fileName
     *
     * @return BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showFile(string $fileName)
    {
        /** @var BinaryFileResponse */
        $fileResponse = $this->showFileService->showFile(ShowFileService::FILE_TYPE_ORDER_RECEIPT, $fileName);

        if (!($fileResponse instanceof BinaryFileResponse)) {
            return $this->render('admin/errors/error404.html.twig');
        }

        return $fileResponse;
    }

    /**
     * @Route("/orders/{id}/order-status/{orderStatus}", requirements={"id"="\d+", "orderStatus"="\d+"}, methods={"PUT"}, name="admin.orders.changeOrderStatus")
     *
     * @ParamConverter("order", options={"mapping"={"id"="id"}})
     * @ParamConverter("orderStatus", options={"mapping"={"orderStatus"="id"}})
     *
     * @param Order $order
     * @param OrderStatus $orderStatus
     * @param TranslatorInterface $translator
     * @param PaymentService $paymentService
     * @param NetsisService $netsisService
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeOrderStatus(
        Order $order,
        OrderStatus $orderStatus,
        TranslatorInterface $translator,
        PaymentService $paymentService,
        NetsisService $netsisService,
        LoggerInterface $logger
    ) {
        $this->denyAccessUnlessGranted([UserVoter::ROLE_ACCOUNTING_TEAM, UserVoter::ROLE_MANAGER], $this->getUser());

        if (!in_array($orderStatus->getId(), [OrderStatus::ID_APPROVED, OrderStatus::ID_PAYMENT_FAILED])) {
            return $this->json(
                [
                    'status' => 'error',
                    'message' => $translator->trans('validation.invalid.parameter'),
                ]
            );
        }

        if (!$order->getOrderStatus()->getId() == OrderStatus::ID_PENDING) {
            $logger->error(sprintf(
                '[OrderController][changeOrderStatusPaidByBankTransfer] Order Payment already processed. orderId: %s, userId: %s, OrderStatusId: %s, RequestOrderStatusId: %s',
                $order->getId(),
                $this->getUser()->getId(),
                $order->getOrderStatus()->getId(),
                $orderStatus->getId()
            ));

            return $this->json(
                [
                    'status' => 'error',
                    'message' => $translator->trans('system.order.blocked_to_change_payment_status'),
                ]
            );
        }

        $order = $this->orderService->prepareOrderSummary($order);

        if ($orderStatus->getId() == OrderStatus::ID_APPROVED) {
            if ($order->getPaymentMethodId() == PaymentService::PAYMENT_METHOD_BANK_TRANSFER) {
                $bankTransferResult = $netsisService->createBankTransfer($order);

                if (($bankTransferResult['success'] ?? false) && ($bankTransferResult['autoApproval'] ?? false)) {
                    if (isset($bankTransferResult['order_id']) && !empty($bankTransferResult['order_id'])) {
                        $order->setNetsisOrderId($bankTransferResult['order_id']);

                        $order = $this->orderService->update($order);

                        if ($order instanceof Order) {
                            $bankTransferApprovedStatusResult = $paymentService->changeBankTransferStatusByPlatformReferenceId($order, $this->getUser(), PaymentService::BANK_TRANSFER_STATUS_APPROVED);

                            if ($bankTransferApprovedStatusResult) {
                                $order->setOrderStatus($orderStatus);

                                $order = $this->orderService->update($order);
                            } else {
                                $logger->error(sprintf(
                                    '[OrderController][changeOrderStatus] Order bank transfer record status could not change. OrderId: %s, BankTransferStatusId: %s, NetsisOrderId: %s',
                                    $order->getId(),
                                    PaymentService::BANK_TRANSFER_STATUS_APPROVED,
                                    $bankTransferResult['order_id'] ?? null
                                ), [
                                    'bankTransferResult' => $bankTransferResult ?? null,
                                ]);

                                if ($orderStatus->getId() == OrderStatus::ID_APPROVED) {
                                    $errorMessage = $translator->trans(
                                        'system.order.bank_transfer_update_failed_with_netsisId',
                                        ['{netsisOrderId}' => $bankTransferResult['order_id'] ?? null]
                                    );
                                } else {
                                    $errorMessage = $translator->trans('system.order.bank_transfer_update_failed');
                                }
                            }
                        } else {
                            $logger->error(
                                sprintf(
                                    '[OrderController][changeOrderStatus] Netsis record created but NetsisOrderId could not save for order paid by bank transfer.
                                 OrderId: %s, NetsisOrderId: %s',
                                    $order->getId(),
                                    $bankTransferResult['order_id'] ?? null
                                ),
                                [
                                    'bankTransferResult' => $bankTransferResult,
                                ]
                            );

                            $errorMessage = $translator->trans(
                                'system.order.order_netsisId_update_failed',
                                ['{netsisOrderId}' => $bankTransferResult['order_id'] ?? null]
                            );
                        }
                    } else {
                        $logger->error(
                            sprintf(
                                '[OrderController][changeOrderStatus] The order paid by bank transfer could not be updated because of NetsisOrderId is empty or not set.
                                 OrderId: %s, NetsisOrderId: %s',
                                $order->getId(),
                                $bankTransferResult['order_id'] ?? null
                            ),
                            [
                                'bankTransferResult' => $bankTransferResult,
                            ]
                        );

                        $errorMessage = $bankTransferResult['message'] ?? $translator->trans('system.order.netsis_order_create_failed');
                    }
                } else {
                    $logger->error(
                        sprintf('[OrderController][changeOrderStatus] Netsis registration failed or autoApproval declined for bank transfer. OrderId: %s', $order->getId()),
                        [
                            'bankTransferResult' => $bankTransferResult,
                        ]
                    );

                    $errorMessage = $bankTransferResult['message'] ?? $translator->trans('system.order.netsis_success_or_autoApproval_failed');
                }
            }

            if ($order->getPaymentMethodId() == PaymentService::PAYMENT_METHOD_FORWARD_SALE) {
                $forwardSaleResult = $netsisService->createForwardSale($order);

                if (($forwardSaleResult['success'] ?? false) && ($forwardSaleResult['autoApproval'] ?? false)) {
                    if (isset($forwardSaleResult['order_id']) && !empty($forwardSaleResult['order_id'])) {
                        $order->setNetsisOrderId($forwardSaleResult['order_id']);

                        $order = $this->orderService->update($order);

                        if ($order instanceof Order) {
                            $order->setOrderStatus($orderStatus);

                            $order = $this->orderService->update($order);
                        } else {
                            $logger->error(
                                sprintf(
                                    '[OrderController][changeOrderStatus] Netsis record created but NetsisOrderId could not save for order paid by forward sale.
                                 OrderId: %s, NetsisOrderId: %s',
                                    $order->getId(),
                                    $forwardSaleResult['order_id'] ?? null
                                ),
                                [
                                    'forwardSaleResult' => $forwardSaleResult,
                                ]
                            );

                            $errorMessage = $translator->trans(
                                'system.order.order_netsisId_update_failed',
                                ['{netsisOrderId}' => $forwardSaleResult['order_id'] ?? null]
                            );
                        }
                    } else {
                        $logger->error(
                            sprintf(
                                '[OrderController][changeOrderStatus] The order paid by forward sale could not be updated because of NetsisOrderId is empty or not set.
                                 OrderId: %s, NetsisOrderId: %s',
                                $order->getId(),
                                $forwardSaleResult['order_id']
                            ),
                            [
                                'forwardSaleResult' => $forwardSaleResult,
                            ]
                        );

                        $errorMessage = $forwardSaleResult['message'] ?? $translator->trans('system.order.netsis_order_create_failed');
                    }
                } else {
                    $logger->error(
                        sprintf('[OrderController][changeOrderStatus] Netsis registration failed or autoApproval declined for forward sale. OrderId: %s', $order->getId()),
                        [
                            'forwardSaleResult' => $forwardSaleResult,
                        ]
                    );

                    $errorMessage = $forwardSaleResult['message'] ?? $translator->trans('system.order.netsis_success_or_autoApproval_failed');
                }
            }

            if ($order instanceof Order && $order->getOrderStatus()->getId() == $orderStatus->getId() && empty($errorMessage)) {
                $this->orderService->updateStocksForCompletedOrders($order);

                $this->addFlash('status', 'success');
                $this->addFlash('message', $translator->trans('system.info.flash_message.success'));

                return $this->json(
                    [
                        'status' => 'success',
                        'message' => $translator->trans('system.info.flash_message.success'),
                    ]
                );
            } else {
                $logger->error(sprintf(
                    '[OrderController][changeOrderStatus] Order status could not change. OrderId: %s',
                    $order->getId()
                ), [
                    'bankTransferResult' => $bankTransferResult ?? null,
                    'forwardSaleResult' => $forwardSaleResult ?? null,
                ]);

                $errorMessage = $errorMessage ?? $translator->trans('system.stock.stock_update_failed');
            }
        }

        if ($orderStatus->getId() == OrderStatus::ID_PAYMENT_FAILED) {
            if ($order->getPaymentMethodId() == PaymentService::PAYMENT_METHOD_BANK_TRANSFER) {
                $bankTransferDeclinedStatusResult = $paymentService->changeBankTransferStatusByPlatformReferenceId($order, $this->getUser(), PaymentService::BANK_TRANSFER_STATUS_DECLINED);

                if (!$bankTransferDeclinedStatusResult) {
                    $logger->error(sprintf(
                        '[OrderController][changeOrderStatus] Order bank transfer record status could not change. OrderId: %s, BankTransferStatusId: %s, NetsisOrderId: %s',
                        $order->getId(),
                        PaymentService::BANK_TRANSFER_STATUS_DECLINED,
                        $bankTransferResult['order_id'] ?? null
                    ), [
                        'bankTransferResult' => $bankTransferResult ?? null,
                    ]);

                    $errorMessage = $translator->trans('system.order.bank_transfer_update_failed');
                }
            }

            $order->setOrderStatus($orderStatus);

            $order = $this->orderService->update($order);

            if ($order instanceof Order) {
                $this->orderService->sendOrderPaymentApprovedMail($order);

                $this->addFlash('status', 'success');
                $this->addFlash('message', $translator->trans('system.info.flash_message.success'));

                return $this->json(
                    [
                        'status' => 'success',
                        'message' => $translator->trans('system.info.flash_message.success'),
                    ]
                );
            } else {
                $logger->error(sprintf(
                    '[OrderController][changeOrderStatus] Order bank transfer record status changed But Order status could not change. OrderId: %s, BankTransferStatusId: %s,',
                    $order->getId(),
                    PaymentService::BANK_TRANSFER_STATUS_DECLINED
                ), [
                    'bankTransferResult' => $bankTransferResult ?? null,
                ]);

                $errorMessage = $translator->trans('system.order.bank_transfer_updated_but_order_update_failed');
            }
        }

        return $this->json(
            [
                'status' => 'error',
                'message' => $errorMessage ?? $translator->trans('system.netsis.error.general_error'),
            ]
        );
    }
}
