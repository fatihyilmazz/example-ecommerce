<?php

namespace App\Controller\Admin;

use App\Entity\Suggestion;
use App\Form\SuggestionType;
use App\Security\Voter\UserVoter;
use App\Service\SuggestionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SuggestionController extends AbstractController
{

    /**
     * @var SuggestionService
     */
    protected $suggestionService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AddressController constructor.
     * @param SuggestionService $suggestionService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SuggestionService $suggestionService,
        TranslatorInterface $translator
    ) {
        $this->suggestionService = $suggestionService;
        $this->translator = $translator;
    }


    /**
     * @Route("suggestions", methods={"GET"}, name="admin.suggestions.index")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        if ($limit > 10) {
            $limit = 10;
        }

        $paginate = $this->suggestionService->paginate($page, $limit);

        return $this->render('admin/suggestions/index.html.twig', [
            'currentPage' => $paginate->getCurrentPage(),
            'totalPage' => $paginate->getNbPages(),
            'totalRecord' => $paginate->getNbResults(),
            'suggestions' => $paginate->getCurrentPageResults(),
            'subjects' => $this->suggestionService->getSubjects(),
            'categories' => $this->suggestionService->getCategories(),
        ]);
    }


    /**
     * @Route("/suggestions/{id}/edit", requirements={"id"="\d+"}, methods={"GET", "PUT"}, name="admin.suggestions.edit")
     *
     * @ParamConverter("suggestion", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Suggestion $suggestion
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Suggestion $suggestion)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $form = $this->createForm(SuggestionType::class, $suggestion, [
            'action' => $this->generateUrl('admin.suggestions.edit', [
                'id' => $suggestion->getId(),
            ]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $suggestion = $this->suggestionService->update($suggestion);

            if ($suggestion instanceof Suggestion) {
                $this->addFlash('status', 'success');
                $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));

                return $this->redirectToRoute('admin.suggestions.index');

            }
            $this->addFlash('status', 'error');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));
        }

        return $this->render('admin/suggestions/edit.html.twig', [
            'form' => $form->createView(),
            'subjects' => $this->suggestionService->getSubjects(),
            'categories' => $this->suggestionService->getCategories(),

        ]);
    }

    /**
     * @Route("/suggestions/{id}", requirements={"id"="\d+"}, methods={"DELETE"}, name="admin.suggestions.delete")
     *
     * @ParamConverter("suggestion", options={"mapping"={"id"="id"}})
     *
     * @param Request $request
     * @param Suggestion $suggestion
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Suggestion $suggestion)
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE_MANAGER, $this->getUser());

        $suggestion = $this->suggestionService->delete($suggestion);

        if ($suggestion instanceof Suggestion) {
            $this->addFlash('status', 'success');
            $this->addFlash('message', $this->translator->trans('system.info.flash_message.success'));
        }

        $this->addFlash('status', 'error');
        $this->addFlash('message', $this->translator->trans('system.info.flash_message.error'));

        return $this->redirectToRoute('admin.suggestions.index');
    }
}
