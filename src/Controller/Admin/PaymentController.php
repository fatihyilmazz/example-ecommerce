<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use App\Entity\EPaymentType;
use App\Service\PaymentService;
use App\Service\MerchantService;
use App\Service\EPaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentController extends AbstractAdminController
{
    /**
     * @var EPaymentService
     */
    protected $ePaymentService;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param EPaymentService $ePaymentService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        EPaymentService $ePaymentService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->ePaymentService = $ePaymentService;
    }

    /**
     * @Route("/payments", methods={"GET"}, name="admin.payments.index")
     *
     * @param Request $request
     *
     * @param MerchantService $merchantService
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function debtPayments(Request $request, MerchantService $merchantService, LoggerInterface $logger)
    {
        $ePaymentTypeId = $request->get('type', null);

        if (!in_array($ePaymentTypeId, [EPaymentType::TRANSFER, EPaymentType::DEBT, EPaymentType::UNKNOWN])) {
            $logger->error(
                sprintf('[PaymentController][debtPayments] Invalid ePaymentTypeId. ePaymentTypeId: %s', $ePaymentTypeId)
            );

            throw $this->createNotFoundException();
        }

        $paymentFilter = $this->ePaymentService->prepareEPaymentFilterWithRequest($request);

        $paymentPaginate = $this->ePaymentService->getEPaymentsWithFilter($paymentFilter);

        return $this->render('admin/payments/index.html.twig', [
            'paymentTypeId' => $ePaymentTypeId,
            'currentPage' => $paymentPaginate->getCurrentPage(),
            'totalPage' => $paymentPaginate->getNbPages(),
            'totalRecord' => $paymentPaginate->getNbResults(),
            'payments' => (array) $paymentPaginate->getCurrentPageResults(),
            'merchants' => $merchantService->getAll(),
            'paymentFilter' => $paymentFilter
        ]);
    }

    /**
     * @Route("/payment-methods", methods={"GET"}, name="admin.payment_methods")
     *
     * @param PaymentService $paymentService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentMethods(PaymentService $paymentService)
    {
        return $this->render('admin/payments/methods.html.twig', [
            'platformPaymentMethods' => $paymentService->getPaymentMethods() ?? [],
        ]);
    }

    /**
     * @Route("/payment-methods/{platformPaymentMethodId}/toggle-status", requirements={"platformPaymentMethodId"="\d+"}, methods={"PUT"}, name="admin.payment_methods.change_status")
     *
     * @param int $platformPaymentMethodId
     * @param PaymentService $paymentService
     * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function togglePaymentMethodStatus(int $platformPaymentMethodId, PaymentService $paymentService)
    {
        return $this->json($paymentService->togglePaymentMethodStatus($platformPaymentMethodId)) ?? false;
    }
}
