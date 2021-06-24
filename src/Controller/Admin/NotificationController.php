<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Service\UserService;
use App\Service\SectorService;
use App\Form\NotificationType;
use App\Service\WebPushService;
use App\Entity\NotificationUser;
use App\Security\Voter\UserVoter;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class NotificationController extends AbstractController
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var WebPushService
     */
    protected $webPushService;

    /**
     * @var SectorService
     */
    private $sectorService;

    /**
     * @param NotificationService $notificationService
     * @param UserService $userService
     * @param WebPushService $webPushService
     * @param SectorService $sectorService
     */
    public function __construct(
        NotificationService $notificationService,
        UserService $userService,
        WebPushService $webPushService,
        SectorService $sectorService
    ) {
        $this->notificationService = $notificationService;
        $this->userService = $userService;
        $this->webPushService = $webPushService;
        $this->sectorService = $sectorService;
    }


    /**
     * @Route("/notifications", methods={"GET"}, name="admin.notifications.index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $notificationFilter = $this->notificationService->prepareFilterNotification($request);

        $notifications = $this->notificationService->getNotificationsWithFilter($notificationFilter);

        return $this->render('admin/notifications/index.html.twig', [
            'currentPage' => $notifications->getCurrentPage(),
            'totalPage' => $notifications->getNbPages(),
            'totalRecord' => $notifications->getNbResults(),
            'notifications' => (array) $notifications->getCurrentPageResults(),
            'notificationFilter'=> $notificationFilter,
            'sectors' => $this->sectorService->getAll(),
        ]);
    }

    /**
     * @Route("/notifications/create", name="admin.notifications.create")
     *
     * @param Request $request
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, SectorService $sectorService)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $notification = new Notification();

        $form = $this->createForm(NotificationType::class, $notification, [
            'action' => $this->generateUrl('admin.notifications.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification = $this->notificationService->create($notification);

            if (($notification instanceof Notification)) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', 'İşlem Başarılı!');

                return $this->redirectToRoute('admin.notifications.index');
            }
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        if (!empty($notification->getSectorIds())) {
            $users = $this->userService->getWebPushUsersBySectorIds($notification->getSectorIds());
        }

        return $this->render('admin/notifications/create.html.twig', [
            'form' => $form->createView(),
            'sectors' => $sectorService->getAll(),
            'users' => $users ?? [],
            'notificationTypes' =>  $this->notificationService->getAllNotificationTypes(),
            'notificationGroups' => $this->notificationService->getAllNotificationGroups()
        ]);
    }

    /**
     * @Route("/notifications/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.notifications.edit")
     *
     * @ParamConverter("notification", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Notification $notification
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Notification $notification, SectorService $sectorService)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $form = $this->createForm(NotificationType::class, $notification, [
            'action' => $this->generateUrl('admin.notifications.edit', ['id' => $notification->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification = $this->notificationService->updateNotification($notification);

            if (!($notification instanceof Notification)) {
                $this->addFlash('status', 'error');
                $this->addFlash('message', 'İşlem Başarısız!');
            } else {
                $this->addFlash('status', 'success');
                $this->addFlash('message', 'İşlem Başarılı!');

                return $this->redirectToRoute('admin.notifications.index');
            }
        }

        $notificationUserIds = $notification->getNotificationUsers()->map(function (NotificationUser $notificationUser) {
            return $notificationUser->getId();
        })->toArray();

        return $this->render('admin/notifications/edit.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
            'sectors' => $sectorService->getAll(),
            'notificationUsers' => $notificationUserIds,
            'users' => $this->userService->getWebPushUsersBySectorIds($notification->getSectorIds()),
            'notificationTypes' =>  $this->notificationService->getAllNotificationTypes(),
            'notificationGroups' => $this->notificationService->getAllNotificationGroups()
        ]);
    }

    /**
     * @Route("/notifications/{id}", requirements={"id"="\d+"}, methods={"DELETE"}, name="admin.notifications.delete")
     *
     * @param Notification $notification
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Notification $notification)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $isDeleted = $this->notificationService->delete($notification);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', 'İşlem Başarılı!');
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', 'İşlem Başarısız!');
        }

        return $this->redirectToRoute('admin.notifications.index');
    }

    /**
     * @Route("/notifications/sectors/{sectorIds}/users", methods={"GET"}, name="admin.notifications.get_users_by_sectors")
     *
     * @param string $sectorIds
     *
     * @return JsonResponse
     */
    public function getUsersBySector(string $sectorIds)
    {
        $sectorIds = explode(',', $sectorIds);

        $usersNameAndEmails = $this->userService->getUsersInformationBySectors($sectorIds);

        if ($usersNameAndEmails instanceof ArrayCollection) {
            return $this->json([
                'status' => 'success',
                'usersNameAndEmails' => $usersNameAndEmails
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Kullanıcı bulunamadı.'
        ]);
    }
}
