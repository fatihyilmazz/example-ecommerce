<?php

namespace App\Controller\Admin;

use App\Entity\Sms;
use App\Form\SmsType;
use App\Entity\SmsGroup;
use App\Form\SmsGroupType;
use App\Service\SmsService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use App\Service\SectorService;
use App\Service\SegmentService;
use App\DTO\SmsGroupAndUserDTO;
use App\DTO\UnregisteredUserDTO;
use App\Security\Voter\UserVoter;
use App\DTO\SmsRegisteredUserDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SmsController extends AbstractController
{
    /**
     * @var SmsService
     */
    protected $smsServices;

    /**
     * @var SegmentService
     */
    protected $segmentService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param SmsService $smsService
     * @param SegmentService $segmentService
     * @param UserService $userService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SmsService $smsService,
        SegmentService $segmentService,
        UserService $userService,
        TranslatorInterface $translator
    ) {
        $this->smsServices = $smsService;
        $this->segmentService = $segmentService;
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @Route("/sms-groups", name="admin.sms_groups.index")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexOfSmsGroups(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->smsServices->paginateSmsGroups($page, $limit);

        return $this->render('admin/sms/groups/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'smsGroups' => (array) $paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("/sms-groups/create", methods={"GET", "POST"}, name="admin.sms_groups.create")
     *
     * @param Request $request
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createSmsGroup(Request $request, SectorService $sectorService)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $smsGroupAndUsers = new SmsGroupAndUserDTO();

        $form = $this->createForm(SmsGroupType::class, $smsGroupAndUsers, [
            'action' => $this->generateUrl('admin.sms_groups.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $smsGroup = $this->smsServices->createSmsGroup($smsGroupAndUsers);

            if ($smsGroup instanceof SmsGroup) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.sms_groups.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sms/groups/create.html.twig', [
            'form' => $form->createView(),
            'allSegments' => $this->segmentService->getAll(),
            'allSectorUsers' => $this->userService->getSectorUsersToSendSms(),
            'sectors' => $sectorService->getActiveSectorIdAndName()->toArray(),
        ]);
    }

    /**
     * @Route("/sms-groups/{id}", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.sms_groups.edit")
     *
     * @ParamConverter("smsGroup", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param SmsGroup $smsGroup
     * @param SectorService $sectorService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editSmsGroup(Request $request, SmsGroup $smsGroup, SectorService $sectorService)
    {
        $smsGroupAndUsersDTO = new SmsGroupAndUserDTO();
        $cloneSmsGroupAndUsersDTO = new SmsGroupAndUserDTO();

        $smsGroupAndUsersDTO->setId($smsGroup->getId());
        $smsGroupAndUsersDTO->setName($smsGroup->getName());
        $smsGroupAndUsersDTO->setIsForAllUser($smsGroup->getIsForAllUser());
        $smsGroupAndUsersDTO->setIsActive($smsGroup->getIsActive());

        $cloneSmsGroupAndUsersDTO->setId($smsGroup->getId());
        $cloneSmsGroupAndUsersDTO->setName($smsGroup->getName());
        $cloneSmsGroupAndUsersDTO->setIsForAllUser($smsGroup->getIsForAllUser());
        $cloneSmsGroupAndUsersDTO->setIsActive($smsGroup->getIsActive());

        if (!$smsGroup->getSmsGroupUsers()->isEmpty()) {
            foreach ($smsGroup->getSmsGroupUsers() as $smsGroupUser) {
                if (!empty($smsGroupUser->getRegisteredUser())) {
                    if (!isset($smsRegisteredUser{$smsGroupUser->getSector()->getName()})) {
                        $smsRegisteredUser{$smsGroupUser->getSector()->getName()} = new SmsRegisteredUserDTO();
                        $smsRegisteredUser{$smsGroupUser->getSector()->getName()}->setSector($smsGroupUser->getSector());
                    }

                    $smsRegisteredUser{$smsGroupUser->getSector()->getName()}->addUser($smsGroupUser->getRegisteredUser());

                    $smsGroupAndUsersDTO->addRegisteredUser($smsRegisteredUser{$smsGroupUser->getSector()->getName()});
                    $cloneSmsGroupAndUsersDTO->addRegisteredUser($smsRegisteredUser{$smsGroupUser->getSector()->getName()});
                } elseif (!empty($smsGroupUser->getSegment())) {
                    $smsGroupAndUsersDTO->addSegment($smsGroupUser->getSegment());
                    $cloneSmsGroupAndUsersDTO->addSegment($smsGroupUser->getSegment());
                } elseif (!empty($smsGroupUser->getPhoneNumber())) {
                    $smsUnregisteredUser = new UnregisteredUserDTO();

                    $smsUnregisteredUser->setFullName($smsGroupUser->getFullName());
                    $smsUnregisteredUser->setPhoneNumber($smsGroupUser->getPhoneNumber());

                    $smsGroupAndUsersDTO->addUnregisteredUser($smsUnregisteredUser);
                    $cloneSmsGroupAndUsersDTO->addUnregisteredUser($smsUnregisteredUser);
                } else {
                    $smsGroupAndUsersDTO->addSector($smsGroupUser->getSector());
                    $cloneSmsGroupAndUsersDTO->addSector($smsGroupUser->getSector());
                }
            }
        }

        $form = $this->createForm(SmsGroupType::class, $smsGroupAndUsersDTO, [
            'action' => $this->generateUrl('admin.sms_groups.edit', [
                'id' => $smsGroup->getId(),
            ]),
            'method' => Request::METHOD_PUT,
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //TODO Testlerde detaylı incelenecek (Remove arrayleri ve cloneSmsGroup'a ihtiyaç olmayabilir.)
            $smsGroup = $this->smsServices->updateSmsGroup($smsGroup, $smsGroupAndUsersDTO, $cloneSmsGroupAndUsersDTO);
            if ($smsGroup instanceof SmsGroup) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.sms_groups.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sms/groups/edit.html.twig', [
            'form' => $form->createView(),
            'allSegments' => $this->segmentService->getAll(),
            'allSectorUsers' => $this->userService->getSectorUsersToSendSms(),
            'sectors' => $sectorService->getAll(),
        ]);
    }

    /**
     * @Route("/sms-groups/{id}", methods={"DELETE"}, name="admin.sms_groups.delete")
     *
     * @ParamConverter("smsGroup", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param SmsGroup $smsGroup
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSmsGroup(Request $request, SmsGroup $smsGroup)
    {
        $isDeleted = $this->smsServices->deleteSmsGroup($smsGroup);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->redirectToRoute('admin.sms_groups.index');
    }

    /**
     * @Route("/sms", methods={"GET"}, name="admin.sms.index")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexOfSms(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->smsServices->paginateSms($page, $limit);

        return $this->render('admin/sms/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'allSms' => (array) $paginate->getCurrentPageResults(),
        ]);
    }

    /**
     * @Route("/sms/create", methods={"GET", "POST"}, name="admin.sms.create")
     *
     * @param Request $request
     * @param KernelInterface $kernel
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createSms(Request $request, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $sms = new Sms();

        $form = $this->createForm(SmsType::class, $sms, [
            'action' => $this->generateUrl('admin.sms.create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sms->setIsCompleted(false);
            $sms = $this->smsServices->createSms($sms);

            if ($sms instanceof Sms) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                if ($sms->getIsActive()) {
                    $application = new Application($kernel);
                    $application->setAutoExit(false);

                    $input = new ArrayInput([
                        'command' => 'sms:send',
                    ]);

                    try {
                        $application->run($input);
                    } catch (\Exception $e) {
                        $logger->warning(sprintf('[SmsController][createSms] %s', $e));
                    } catch (\Error $e) {
                        $logger->error(sprintf('[SmsController][createSms] %s', $e));
                    }
                }

                return $this->redirectToRoute('admin.sms.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sms/create.html.twig', [
            'form' => $form->createView(),
            'smsGroups' => $this->smsServices->getAllGroups(),
            'transactionTypes' => $this->smsServices->getTransactionTypes(),
            'mersisNoAndSmsCanceledMessage' => $this->smsServices->getMersisNoAndSmsCancelMessage(),
        ]);
    }

    /**
     * @Route("/sms/{id}", methods={"GET", "PUT"}, name="admin.sms.edit")
     *
     * @ParamConverter("sms", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Sms $sms
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editSms(Request $request, Sms $sms, KernelInterface $kernel, LoggerInterface $logger)
    {
        $form = $this->createForm(SmsType::class, $sms, [
            'action' => $this->generateUrl('admin.sms.edit', [
                'id' => $sms->getId(),
            ]),
            'method' => Request::METHOD_PUT,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sms = $this->smsServices->updateSms($sms);

            if ($sms instanceof Sms) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                if ($sms->getIsActive()) {
                    $application = new Application($kernel);
                    $application->setAutoExit(false);

                    $input = new ArrayInput([
                        'command' => 'sms:send',
                    ]);

                    try {
                        $application->run($input);
                    } catch (\Exception $e) {
                        $logger->warning(sprintf('[SmsController][editSms] %s', $e));
                    } catch (\Error $e) {
                        $logger->error(sprintf('[SmsController][editSms] %s', $e));
                    }
                }

                return $this->redirectToRoute('admin.sms.index');
            }

            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->render('admin/sms/edit.html.twig', [
            'form' => $form->createView(),
            'smsGroups' => $this->smsServices->getAllGroups(),
            'transactionTypes' => $this->smsServices->getTransactionTypes(),
            'mersisNoAndSmsCanceledMessage' => $this->smsServices->getMersisNoAndSmsCancelMessage(),
        ]);
    }

    /**
     * @Route("/sms/{id}", methods={"DELETE"}, name="admin.sms.delete")
     *
     * @ParamConverter("sms", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Sms $sms
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSms(Request $request, Sms $sms)
    {
        $isDeleted = $this->smsServices->deleteSms($sms);
        if ($isDeleted) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));
        } else {
            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));
        }

        return $this->redirectToRoute('admin.sms.index');
    }
}
