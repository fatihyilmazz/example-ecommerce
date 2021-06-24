<?php

namespace App\Controller\Api;

use App\Entity\Sector;
use App\Service\UserService;
use App\Service\SectorService;
use App\Service\MerchantService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SectorController extends AbstractApiController
{
    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        SectorService $sectorService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->sectorService = $sectorService;
    }


    /**
     * @Route("sectors/{sectorId}/merchants/active-merchants", requirements={"sectorId"="\d+"}, methods={"GET"}, name="front.sectors.get_active_merchants")
     *
     * @Entity("sector", expr="repository.findSectorByPMSectorId(sectorId)")
     *
     * @param Request $request
     * @param Sector $sector
     * @param MerchantService $merchantService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function getMerchantIdAndNameListBySector(Request $request, Sector $sector, MerchantService $merchantService)
    {
        return $this->json([
            'merchants' => $merchantService->getMerchantIdAndNameListBySector($sector) ?? [],
        ]);
    }

    /**
     * @Route("sectors/{sectorId}/users/active-users", requirements={"sectorId"="\d+"}, methods={"GET"}, name="front.sectors.get_active_users")
     *
     * @Entity("sector", expr="repository.findSectorByPMSectorId(sectorId)")
     *
     * @param Request $request
     * @param Sector $sector
     * @param UserService $userService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function getUserIdAndNameListBySector(Request $request, Sector $sector, UserService $userService)
    {
        return $this->json([
            'users' => $userService->getUserIdAndNameListBySector($sector) ?? [],
        ]);
    }
}
