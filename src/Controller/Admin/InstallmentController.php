<?php

namespace App\Controller\Admin;

use App\Form\InstallmentType;
use App\Service\PaymentService;
use App\DTO\InstallmentDetailDTO;
use App\Security\Voter\UserVoter;
use App\Service\InstallmentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InstallmentController extends AbstractController
{
    /**
     * @var PaymentService
     */
    protected $installmentService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        InstallmentService $installmentService,
        TranslatorInterface $translator
    ) {
        $this->installmentService = $installmentService;
        $this->translator = $translator;
    }


    /**
     * @Route("installments", methods={"GET"}, name="admin.installments.index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function index()
    {
        $installments = $this->installmentService->getInstallmentsByPlatformId();

        $bankInstallmentGroups = [];
        foreach ($installments as $installment) {
            if (count($bankInstallmentGroups) == 0  || !array_key_exists($installment['bank']['name'], $bankInstallmentGroups)) {
                $bankInstallmentGroups[$installment['bank']['name']][] = $installment;
            } elseif (array_key_exists($installment['bank']['name'], $bankInstallmentGroups)) {
                $bankInstallmentGroups[$installment['bank']['name']][] = $installment;
            }
        }

        return $this->render('admin/installments/index.html.twig', [
            'bankInstallmentGroups' => $bankInstallmentGroups,
        ]);
    }

    /**
     * @Route("/installments/create",
     *     methods={"GET", "POST"},
     *     name="admin.installments.create")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_ACCOUNTING_TEAM, $this->getUser());

        $instalmentDetailDTO = new InstallmentDetailDTO();

        $form = $this->createForm(InstallmentType::class, $instalmentDetailDTO, [
            'action' => $this->generateUrl('admin.installments.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->installmentService->createInstallmentDetail($instalmentDetailDTO);

            if ($result['success']) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.installments.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $result['message']);
        }

        $banks = $this->installmentService->getBanks();
        $cardFamilies = $this->installmentService->getCardFamilies();
        $gateways = $this->installmentService->getGateways();

        return $this->render('admin/installments/create.html.twig', [
            'form' => $form->createView(),
            'banks' => $banks,
            'cardFamilies' => $cardFamilies,
            'gateways' => $gateways,
        ]);
    }


    /**
     * @Route("/installments/{installmentId}/edit",
     *     requirements={"installmentId"="\d+"},
     *     methods={"GET", "POST"},
     *     name="admin.installments.edit")
     *
     * @param Request $request
     * @param int $installmentId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function edit(Request $request, int $installmentId)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_ACCOUNTING_TEAM, $this->getUser());

        $installment = $this->installmentService->getInstallmentDetailsById($installmentId);

        $instalmentDetailDTO = new InstallmentDetailDTO();
        $instalmentDetailDTO->setId((int)$installment['id']);
        $instalmentDetailDTO->setBankId((int)$installment['bank']['id']);
        $instalmentDetailDTO->setCardFamilyId((int)$installment['card_family']['id']);
        $instalmentDetailDTO->setGatewayId((int)$installment['gateway']['id']);
        $instalmentDetailDTO->setNumber((int)$installment['number']);
        $instalmentDetailDTO->setCommissionRate((float)$installment['commission_rate']);
        $instalmentDetailDTO->setIsActive((bool)$installment['is_active']);

        $form = $this->createForm(InstallmentType::class, $instalmentDetailDTO, [
            'action' => $this->generateUrl('admin.installments.edit', [
                'installmentId' => $installmentId,
            ]),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->installmentService->updateInstallmentDetail($instalmentDetailDTO);

            if ($result['success']) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.installments.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $result['message']);
        }

        $banks = $this->installmentService->getBanks();
        $cardFamilies = $this->installmentService->getCardFamilies();
        $gateways = $this->installmentService->getGateways();

        return $this->render('admin/installments/edit.html.twig', [
            'form' => $form->createView(),
            'banks' => $banks,
            'cardFamilies' => $cardFamilies,
            'gateways' => $gateways,
        ]);
    }

    /**
     * @Route("/installments/{installmentId}",
     *      requirements={"installmentId"="\d+"},
     *      methods={"DELETE"},
     *      name="admin.installments.delete")
     *
     * @param int $installmentId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function delete(int $installmentId)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_ACCOUNTING_TEAM, $this->getUser());

        $isDeleted = $this->installmentService->deleteInstallmentDetail($installmentId);

        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->redirectToRoute('admin.installments.index');
    }
}
