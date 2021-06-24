<?php

namespace App\Controller\Api;

use App\Service\MerchantService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MerchatController extends AbstractApiController
{
    /**
     * @var MerchantService
     */
    protected $merchantService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param MerchantService $merchantService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        MerchantService $merchantService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->merchantService = $merchantService;
    }

    /**
     * @Route("/merchants/selected-merchants", methods={"GET"}, name="front.merchants.selected_merchants")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelectedMerchantIdAndName(Request $request)
    {
        $merchantIds = $request->query->get('selectedMerchants');
        $merchants = $this->merchantService->getSelectedMerchantsIdNameAndMail($merchantIds);

        return $this->json([
            'merchants' => $merchants ?? []
        ]);
    }
}
