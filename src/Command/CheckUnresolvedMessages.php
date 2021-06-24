<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use App\Service\MailService;
use App\Entity\MerchantContactMessage;
use App\Service\MerchantContactService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Console\Command\Command;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckUnresolvedMessages extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MerchantContactService
     */
    protected $merchantContactService;

    /**
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    protected static $defaultName = 'notification:check-unresolved-messages';

    /**
     * @param EntityManagerInterface $entityManager
     * @param MerchantContactService $merchantContactService
     * @param TwigEnvironment $twig
     * @param LoggerInterface $logger
     * @param MailService $mailService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MerchantContactService $merchantContactService,
        TwigEnvironment $twig,
        LoggerInterface $logger,
        MailService $mailService,
        TranslatorInterface $translator
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->merchantContactService = $merchantContactService;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->mailService = $mailService;
        $this->translator = $translator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks unresolved messages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $unresolvedMessages = $this->merchantContactService->getUnresolvedMessages();

        if (empty($unresolvedMessages)) {
            $io->success('[CheckUnresolvedMessages][execute] No unresolved messages found.');

            return;
        }

        $sentEmailsToUser = new ArrayCollection();
        $sentEmailsToMerchant = new ArrayCollection();
        $sentEmailMessages = new ArrayCollection();

        foreach ($unresolvedMessages as $unresolvedMessage) {
            /** @var MerchantContactMessage $unresolvedMessage */
            if ($sentEmailMessages->contains($unresolvedMessage->getMerchantContact()->getId())) {
                continue;
            }

            $sentEmailMessages->add($unresolvedMessage->getMerchantContact()->getId());

            if ($unresolvedMessage->getIsMerchant()) {
                $sendEmailToUser = $this->sendEmailToUser($unresolvedMessage, $sentEmailsToUser, $io);
                if (!$sendEmailToUser) {
                    $logMessage = sprintf(
                        '[CheckUnresolvedMessages][execute] There is a problem to send email to %s address.',
                        $unresolvedMessage->getMerchantContact()->getUser()->getEmail()
                    );

                    $io->error($logMessage);
                }
                continue;
            }

            $sendEmailToSellerMerchant = $this->sendEmailToSellerMerchant($unresolvedMessage, $sentEmailsToMerchant, $io);
            if (!$sendEmailToSellerMerchant) {
                $logMessage = sprintf(
                    '[CheckUnresolvedMessages][execute] There is a problem to send email to %s address.',
                    $unresolvedMessage->getMerchantContact()->getMerchant()->getEmail()
                );

                $io->error($logMessage);
            }
        }

        $logMessage = '[CheckUnresolvedMessages][execute] Email sent to users and merchants';

        $this->logger->info($logMessage, [
            'userIds' => $sentEmailsToUser->toArray(),
            'merchantIds' => $sentEmailsToMerchant->toArray()
        ]);

        $io->success($logMessage);
    }

    /**
     * @param MerchantContactMessage $unresolvedMessage
     * @param ArrayCollection $sentEmailsToMerchant
     * @param SymfonyStyle $io
     *
     * @return bool
     */
    public function sendEmailToSellerMerchant(MerchantContactMessage $unresolvedMessage, ArrayCollection $sentEmailsToMerchant, SymfonyStyle $io)
    {
        try {
            if (!$sentEmailsToMerchant->contains($unresolvedMessage->getMerchantContact()->getMerchant()->getId())) {
                $sentEmailsToMerchant->add($unresolvedMessage->getMerchantContact()->getMerchant()->getId());

                $template = $this->twig->render('commons/mails/unresolved_messages.html.twig', [
                    'userMerchantQuestionsLink' => 'front.marketplace.messages.index',
                    'message' => $unresolvedMessage,
                    'toMerchant' => true,
                ]);

                $merchantMailResult = $this->mailService->send(
                    $unresolvedMessage->getMerchantContact()->getMerchant()->getEmail(),
                    $this->translator->trans('system.info.messages.unresolved_message_info'),
                    $template
                );

                if ($merchantMailResult) {
                    $merchantContactMessage = $this->merchantContactService->setEmailSendToMessages($unresolvedMessage);
                    if ($merchantContactMessage) {
                        return true;
                    }
                }

                return false;
            }

            $logMessage = sprintf(
                '[CheckUnresolvedMessages][sendEmailToSellerMerchant] Email already sent to %s.',
                $unresolvedMessage->getMerchantContact()->getMerchant()->getEmail()
            );

            $io->success($logMessage);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[CheckUnresolvedMessages][sendEmailToSellerMerchant] %s', $e), [
                'unresolvedMessageId' => $unresolvedMessage->getId(),
            ]);
        }

        return false;
    }

    /**
     * @param MerchantContactMessage $unresolvedMessage
     * @param ArrayCollection $sentEmailsToUser
     * @param SymfonyStyle $io
     *
     * @return bool
     */
    public function sendEmailToUser(MerchantContactMessage $unresolvedMessage, ArrayCollection $sentEmailsToUser, SymfonyStyle $io)
    {
        try {
            if (!$sentEmailsToUser->contains($unresolvedMessage->getMerchantContact()->getUser()->getId())) {
                $sentEmailsToUser->add($unresolvedMessage->getMerchantContact()->getUser()->getId());

                $template = $this->twig->render('commons/mails/unresolved_messages.html.twig', [
                    'userMerchantQuestionsLink' => 'front.users.product_questions',
                    'message' => $unresolvedMessage,
                    'toMerchant' => false,
                ]);

                $userMailResult = $this->mailService->send(
                    $unresolvedMessage->getMerchantContact()->getUser()->getEmail(),
                    $this->translator->trans('system.info.messages.unresolved_message_info'),
                    $template
                );

                if ($userMailResult) {
                    $merchantContactMessage = $this->merchantContactService->setEmailSendToMessages($unresolvedMessage);
                    if ($merchantContactMessage) {
                        return true;
                    }
                }

                return false;
            }

            $logMessage = sprintf(
                '[CheckUnresolvedMessages][sendEmailToSellerMerchant] Email already sent to %s.',
                $unresolvedMessage->getMerchantContact()->getMerchant()->getEmail()
            );

            $io->success($logMessage);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[CheckUnresolvedMessages][sendEmailToUser] %s', $e), [
                'unresolvedMessageId' => $unresolvedMessage->getId()
            ]);
        }

        return false;
    }
}
