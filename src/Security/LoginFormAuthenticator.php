<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Sector;
use App\Entity\Merchant;
use Psr\Log\LoggerInterface;
use App\Entity\MerchantSector;
use App\Entity\MerchantSectorStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function supports(Request $request)
    {
        return 'auth.login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            /*'csrf_token' => $request->request->get('_csrf_token'),*/
            'ipAddress' => $request->getClientIp(),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $credentials['email'],
        ]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException($this->translator->trans('system.login.user_not_found'));
        } elseif ($user->isActive() == false) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('system.login.passive_user'));
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $isValidPassword = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        if (!$isValidPassword) {
            $isValidMD5Password = $user->getMd5Password() === md5($credentials['password']);

            if ($isValidMD5Password) {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $credentials['password']));
                $user->setMd5Password(null);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return true;
            }
        }

        $merchantStatus = false;

        if ($isValidPassword) {
            $merchantStatus = $this->checkMerchantAndUserStatus($user);
        }

        if ($merchantStatus) {
            $user->setLastLoginAt(new \DateTime());
            $user->setIpAddress($credentials['ipAddress']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $merchantStatus;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('front.home.index'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('auth.login');
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkMerchantAndUserStatus(UserInterface $user)
    {
        /** @var Merchant $merchant */
        $merchant = $user->getMerchant();

        if ($merchant instanceof Merchant) {
            $merchantResult = $this->isMerchantAllowedToLogIn($merchant);
        }

        if ($merchantResult ?? false) {
            $userResult = $this->isUserAllowedToLogIn($user);
        }

        if ($userResult ?? false) {
            $merchantSectorResult = $this->isMerchantSectorsAllowedToLogIn(
                $merchant->getMerchantSectors(),
                $user
            );
        }

        return $merchantSectorResult ?? false;
    }

    /**
     * @param Merchant $merchant
     *
     * @return bool
     */
    public function isMerchantAllowedToLogIn(Merchant $merchant): bool
    {
        if (!empty($merchant->getVerifiedAt()) && empty($merchant->getDeletedAt()) && $merchant->isActive()) {
            return true;
        }

        return false;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isUserAllowedToLogIn(UserInterface $user): bool
    {
        if (empty($user->getDeletedAt()) && $user->isActive()) {
            return true;
        }

        return false;
    }

    /**
     * @param Collection $merchantSectors
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isMerchantSectorsAllowedToLogIn(Collection $merchantSectors, UserInterface $user): bool
    {
        /** @var Sector $userDefaultSector */
        $userDefaultSector = $user->getDefaultSector();

        /** @var MerchantSector $merchantSector */
        foreach ($merchantSectors as $merchantSector) {
            if ($userDefaultSector->getId() == $merchantSector->getSector()->getId()) {
                if (empty($merchantSector->getSector()->getDeletedAt()) &&
                    $merchantSector->getSector()->isActive() &&
                    empty($merchantSector->getDeletedAt()) &&
                    $merchantSector->isActive() &&
                    $merchantSector->getMerchantSectorStatus()->getId() == MerchantSectorStatus::STATUS_TYPE_ACTIVE_ID
                ) {
                    return true;
                }
            }
        }

        foreach ($merchantSectors as $merchantSector) {
            if (empty($merchantSector->getSector()->getDeletedAt()) &&
                $merchantSector->getSector()->isActive() &&
                empty($merchantSector->getDeletedAt()) &&
                $merchantSector->isActive() &&
                $merchantSector->getMerchantSectorStatus()->getId() == MerchantSectorStatus::STATUS_TYPE_ACTIVE_ID
            ) {
                $user->setDefaultSector($merchantSector->getSector());

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return true;
            }
        }

        $this->logger->emergency(
            sprintf(
                '[LoginFormAuthenticator][checkMerchantSectorStatues] Active sector not found for merchant. The user was not allowed to log on. UserId: %s, MechantId: %s',
                $user->getId(),
                $user->getMerchant()->getId()
            )
        );

        return false;
    }
}
