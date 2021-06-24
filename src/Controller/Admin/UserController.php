<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Merchant;
use App\Service\UserService;
use App\Service\RoleService;
use App\Service\SectorService;
use App\Service\BasketService;
use App\Service\MerchantService;
use App\Security\Voter\UserVoter;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractAdminController
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var MerchantService
     */
    protected $merchantService;

    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserService $userService
     * @param MerchantService $merchantService
     * @param RoleService $roleService
     * @param SectorService $sectorService
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        UserService $userService,
        MerchantService $merchantService,
        RoleService $roleService,
        SectorService $sectorService
    ) {
        parent::__construct($serializer, $eventDispatcher);

        $this->userService = $userService;
        $this->merchantService = $merchantService;
        $this->roleService = $roleService;
        $this->sectorService = $sectorService;
    }

    /**
     * @Route("/users", methods={"GET"}, name="admin.users.index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $userFilter = $this->userService->prepareUserFilterWithRequest($request);

        $userPaginate = $this->userService->getUsersWithFilter($userFilter);

        return $this->render('admin/users/index.html.twig', [
            'currentPage' => $userPaginate->getCurrentPage(),
            'totalPage' => $userPaginate->getNbPages(),
            'totalRecord' => $userPaginate->getNbResults(),
            'users' => (array) $userPaginate->getCurrentPageResults(),
            'merchants' => $this->merchantService->getAll(),
            'userFilter' => $userFilter,
            'sectors' => $this->sectorService->getAll()
        ]);
    }

    /**
     * @Route("/users/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.users.edit")
     *
     * @ParamConverter("users", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User $user
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $userPasswordEncoder, SectorService $sectorService)
    {
        $previousUser = clone $user;

        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('admin.users.edit', ['id' => $user->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

            $password = $request->get('password', []);
            if (array_key_exists('first', $password) && !empty($password['first'])) {
                $newPassword = $userPasswordEncoder->encodePassword($user, $user->getPassword());

                $user->setPassword($newPassword);
            }

            $user = $this->userService->updateUserWithEvent($user, $previousUser);

            if ($user instanceof User) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', 'İşlem Başarılı!');

                return $this->redirectToRoute('admin.users.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'merchants' => $this->merchantService->getAll(),
            'roles' => $this->roleService->getMerchantRoleAsCollection($this->getUser()),
            'sectors' => $sectorService->getAll(),

        ]);
    }

    /**
     * @Route("/users/{id}", requirements={"id"="\d+"}, methods={"DELETE"}, name="admin.users.delete")
     *
     * @ParamConverter("user", options={"mapping"={"id"="id"}})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $isDeleted = $this->userService->delete($user);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', 'İşlem Başarılı!');
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        return $this->redirectToRoute('admin.users.index');
    }

    /**
     * @Route("/users/search", methods={"GET"}, name="admin.users.search")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function search(Request $request)
    {
        $searchKey = $request->get('keyword');

        $users = $this->userService->search($searchKey);

        return $this->json(
            [
                'users' => $users,
            ]
        );
    }

    /**
     * @Route("/users/pending-baskets", methods={"GET"}, name="admin.users.pending_baskets")
     *
     * @param Request $request
     * @param BasketService $basketService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pendingBaskets(Request $request, BasketService $basketService)
    {
        $basketFilter = $basketService->prepareBasketFilterWithRequest($request);

        $basketPaginate = $basketService->getBasketsWithFilter($basketFilter);

        return $this->render('admin/users/pending_baskets.html.twig', [
            'currentPage' => $basketPaginate->getCurrentPage(),
            'totalPage' => $basketPaginate->getNbPages(),
            'totalRecord' => $basketPaginate->getNbResults(),
            'pendingBaskets' => (array) $basketPaginate->getCurrentPageResults(),
            'basketFilter' => $basketFilter,
            'merchants' => $this->merchantService->getMerchantsIdAndName(),
            'users' => $this->userService->getUsersIdAndName(),
            'sectors' => $this->sectorService->getAll()
        ]);
    }
}
