<?php

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TwigEnvironment
     */
    protected $twig;

    public function __construct(
        LoggerInterface $logger,
        TwigEnvironment $twig
    ) {
        $this->logger = $logger;
        $this->twig = $twig;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $htmlContent = $this->twig->render('commons\authorization\access_denied.html.twig');

        $this->logger->warning(sprintf('[AccessDeniedHandler][handle] %s', $accessDeniedException));

        if ($accessDeniedException->getSubject() instanceof User) {
            $this->logger->warning(sprintf('[AccessDeniedHandler][handle] userId %s', $accessDeniedException->getSubject()->getId()));
        }

        if (!empty($accessDeniedException->getAttributes())) {
            $this->logger->warning(sprintf('[AccessDeniedHandler][handle] attributes %s', json_encode($accessDeniedException->getAttributes())));
        }

        return new Response($htmlContent, Response::HTTP_FORBIDDEN);
    }
}
