<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use App\Entity\Notification;
use App\Service\WebPushService;
use App\Entity\NotificationUser;
use App\Entity\NotificationGroup;
use App\Service\WebPushTokenService;
use App\Service\NotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationWebPushCommand extends Command
{
    protected static $defaultName = 'notification:web-push';

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var WebPushService
     */
    protected $webPushService;

    /**
     * @var WebPushTokenService
     */
    protected $webPushTokenService;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ContainerInterface $container
     * @param NotificationService $notificationService
     * @param WebPushService $webPushService
     * @param WebPushTokenService $webPushTokenService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContainerInterface $container,
        NotificationService $notificationService,
        WebPushService $webPushService,
        WebPushTokenService $webPushTokenService,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->notificationService = $notificationService;
        $this->webPushService = $webPushService;
        $this->webPushTokenService = $webPushTokenService;
        $this->container = $container;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Send web push notifications for users');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $webPushNotifications = $this->notificationService->getPendingWebPushNotifications();
        $notifiedWebPushNotificationIds = [];

        if ($webPushNotifications->isEmpty()) {
            $logMessage = '[NotificationWebPushCommand][execute] There is not any web push notification';

            $this->logger->info($logMessage);

            $io->success($logMessage);

            return;
        }

        foreach ($webPushNotifications as $webPushNotification) {
            switch ($webPushNotification->getNotificationGroup()->getId()) {
                case NotificationGroup::ID_ALL_USERS:
                    $webPushNotification = $this->notifyAllUsers($webPushNotification);
                    break;
                case NotificationGroup::ID_SPECIFIED_USERS:
                    $webPushNotification = $this->notifySpecifiedUsers($webPushNotification, $io);
                    break;
                case NotificationGroup::ID_SELLER_MERCHANT_USERS:
                    $webPushNotification =  $this->notifySellerMerchantUsers($webPushNotification);
                    break;
                default:
                    $this->logger->warning('[NotificationWebPushCommand][execute] Undefined group id', [
                        'webPushNotificationGroupId' => $webPushNotification->getNotificationGroup()->getId()
                    ]);
                    break;
            }

            if ($webPushNotification instanceof Notification) {
                $webPushNotification = $this->notificationService->updateNotification($webPushNotification);

                if ($webPushNotification instanceof Notification) {
                    $notifiedWebPushNotificationIds[] = $webPushNotification->getId();

                    $io->success(sprintf(
                        'Notification submission success. NotificationId: %s',
                        $webPushNotification->getId()
                    ));
                } else {
                    $logMessage = sprintf(
                        '[NotificationWebPushCommand][execute] All Notifications was notified but notification status could not change %s',
                        $webPushNotification->getId()
                    );

                    $this->logger->error($logMessage);

                    $io->error($logMessage);
                }
            } else {
                $logMessage = sprintf('[NotificationWebPushCommand][execute] Failed to send notification - undefined notification group id');

                $this->logger->error($logMessage);

                $io->error($logMessage);
            }
        }
        if (!empty($notifiedWebPushNotificationIds)) {
            $this->logger->info('[NotificationWebPushCommand][execute] Notifications notified to users', [
                'webPushNotificationIds' => $notifiedWebPushNotificationIds
            ]);
        }
    }


    /**
     * @param Notification $webPushNotification
     *
     * @return Notification|null
     */
    protected function notifyAllUsers(Notification $webPushNotification)
    {
        try {
            $webPushTokens = $this->webPushTokenService->getUsersWebPushTokensBySectorIds($webPushNotification->getSectorIds());

            foreach ($webPushTokens as $webPushToken) {
                $this->webPushService->send(
                    $webPushToken->getToken(),
                    $webPushNotification->getTitle(),
                    $webPushNotification->getContent(),
                    sprintf(
                        'https://%s/assets/images/push_icon.png',
                        $this->container->getParameter('base_url')
                    ),
                    $webPushNotification->getLink()
                );
            }
            $webPushNotification->setIsNotified(true);

            return $webPushNotification;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[NotificationWebPushCommand][sendAllUsers] %s', $e), [
                'webPushNotificationId' => $webPushNotification->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Notification $webPushNotification
     * @param SymfonyStyle $io
     *
     * @return Notification|null
     */
    protected function notifySpecifiedUsers(Notification $webPushNotification, SymfonyStyle $io)
    {
        try {
            $notificationUsers = $webPushNotification->getNotificationUsers();

            foreach ($notificationUsers as $notificationUser) {
                if ($notificationUser->isNotified() == false) {
                    $userWebPushTokens = $notificationUser->getUser()->getWebPushTokens();

                    foreach ($userWebPushTokens as $userWebPushToken) {
                        $this->webPushService->send(
                            $userWebPushToken->getToken(),
                            $webPushNotification->getTitle(),
                            $webPushNotification->getContent(),
                            sprintf(
                                'https://%s/assets/images/push_icon.png',
                                $this->container->getParameter('base_url')
                            ),
                            $webPushNotification->getLink()
                        );
                    }
                    $notificationUser->setIsNotified(true);

                    $notificationUser = $this->notificationService->updateNotificationUser($notificationUser);

                    if (!($notificationUser instanceof NotificationUser)) {
                        $logMessage = sprintf(
                            '[NotificationWebPushCommand][sendSpecificUsers] Notification was send but user status could not change. NotificationId: %s, UserId: %s',
                            $webPushNotification->getId(),
                            $notificationUser->getUser()->getId()
                        );

                        $this->logger->error($logMessage);
                        $io->error($logMessage);
                    }
                }
            }
            $webPushNotification->setIsNotified(true);

            return $webPushNotification;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[NotificationWebPushCommand][sendSpecificUsers] %s', $e), [
                'webPushNotificationId' => $webPushNotification->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Notification $webPushNotification
     *
     * @return Notification|null
     */
    protected function notifySellerMerchantUsers(Notification $webPushNotification)
    {
        try {
            $sellerMerchantUserTokens = $this->webPushTokenService->getSellerMerchantUsersWebPushTokensBySector($webPushNotification->getSectorIds());

            foreach ($sellerMerchantUserTokens as $sellerMerchantUserToken) {
                $this->webPushService->send(
                    $sellerMerchantUserToken->getToken(),
                    $webPushNotification->getTitle(),
                    $webPushNotification->getContent(),
                    sprintf(
                        'https://%s/assets/images/push_icon.png',
                        $this->container->getParameter('base_url')
                    ),
                    $webPushNotification->getLink()
                );
            }
            $webPushNotification->setIsNotified(true);

            return $webPushNotification;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[NotificationWebPushCommand][sendSellerMerchantUsers] %s', $e), [
                'webPushNotificationId' => $webPushNotification->getId(),
            ]);
        }

        return null;
    }
}
