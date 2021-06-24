<?php

namespace App\Controller\Front;

use App\Entity\Basket;
use Psr\Log\LoggerInterface;
use App\Service\BasketService;
use App\Service\AddressService;
use App\Form\BasketAddressType;
use App\Service\AddressTypeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddressController extends AbstractController
{
    /**
     * @var \App\Service\BasketService
     */
    protected $basketService;

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var AddressTypeService
     */
    protected $addressTypeService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param BasketService $basketService
     * @param AddressService $addressService
     * @param AddressTypeService $addressTypeService
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(
        BasketService $basketService,
        AddressService $addressService,
        AddressTypeService $addressTypeService,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->basketService = $basketService;
        $this->addressService = $addressService;
        $this->addressTypeService = $addressTypeService;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @Route("/adres-secimi", methods={"GET", "POST"}, name="front.addresses.chooseAddress")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function chooseAddress(Request $request)
    {
        $basket = $this->basketService->getCurrentBasketByUser($this->getUser());

        if (!($basket instanceof Basket)) {
            $this->logger->error(
                sprintf(
                    '[AddressController][chooseAddress] Basket not found. User Id: %s',
                    $this->getUser()->getId()
                )
            );

            throw new \LogicException('[AddressController][chooseAddress]');
        }

        $basket = $this->basketService->getBasketSummary($basket);

        if ($basket->getBasketProducts()->isEmpty()) {
            return $this->redirectToRoute('front.home.index');
        }

        $form = $this->createForm(BasketAddressType::class, $basket, [
            'action' => $this->generateUrl('front.addresses.chooseAddress'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('addressType') == 0) {
                $basket->setShippingAddress($basket->getBillingAddress());
            }

            $basket = $this->addressService->addAddressesToBasket($basket);
            if ($basket instanceof Basket) {
                return $this->redirectToRoute('front.checkout.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.basket.add_address.error'));
        }

        return $this->render('front/checkout/choose_address.html.twig', [
            'form' => $form->createView(),
            'addresses' => $this->addressService->getAddressesOfMerchant($this->getUser()->getMerchant()),
            'addressTypes' => $this->addressTypeService->getAddressTypesWithoutBilling(),
            'numberOfBasketProducts' => $this->basketService->getCurrentBasketByUser($this->getUser())->getBasketProducts()->count(),
            'basketSummary' => $basket->getSummary()
        ]);
    }
}
