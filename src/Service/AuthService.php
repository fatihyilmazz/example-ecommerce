<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Address;
use App\Utils\Generate;
use App\Entity\Merchant;
use App\Entity\AddressType;
use Psr\Log\LoggerInterface;
use App\Utils\PhoneNumberUtil;
use App\Security\Voter\UserVoter;
use App\Entity\MerchantSectorStatus;
use App\Event\MerchantRegisteredEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService extends AbstractService
{
    /**
     * @var SectorService
     */
    protected $sectorService;

    /**
     * @var AddressTypeService
     */
    protected $addressTypeService;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param SectorService $sectorService
     * @param AddressTypeService $addressTypeService
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        SectorService $sectorService,
        AddressTypeService $addressTypeService,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        parent::__construct($container, $logger);

        $this->sectorService = $sectorService;
        $this->addressTypeService = $addressTypeService;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param Merchant $merchant
     * @param User $user
     * @param Address $address
     * @param Request $request
     * @param FormInterface $form
     *
     * @return Merchant|null
     */
    public function register(Merchant $merchant, User $user, Address $address, Request $request, FormInterface $form)
    {
        try {
            $contractFile = $form['contractFile']->getData();
            $signatureFile = $form['signatureFile']->getData();
            $taxFile = $form['taxFile']->getData();
            $journalFile = $form['journalFile']->getData();

            $contractFileName = sprintf(
                '%s-%s.%s',
                'contract',
                uniqid(),
                $contractFile->guessExtension()
            );

            $signatureFileName = sprintf(
                '%s-%s.%s',
                'signature',
                uniqid(),
                $signatureFile->guessExtension()
            );

            $taxFileName = sprintf(
                '%s-%s.%s',
                'tax',
                uniqid(),
                $taxFile->guessExtension()
            );

            $journalFileName = sprintf(
                '%s-%s.%s',
                'journal',
                uniqid(),
                $journalFile->guessExtension()
            );

            $contractFile->move($this->container->getParameter('merchant_contract_file_directory'), $contractFileName);
            $signatureFile->move($this->container->getParameter('merchant_signature_file_directory'), $signatureFileName);
            $taxFile->move($this->container->getParameter('merchant_tax_file_directory'), $taxFileName);
            $journalFile->move($this->container->getParameter('merchant_journal_file_directory'), $journalFileName);

            $merchant->setContractFile($contractFileName);
            $merchant->setSignatureFile($signatureFileName);
            $merchant->setTaxFile($taxFileName);
            $merchant->setJournalFile($journalFileName);

            $merchant->getMerchantSectors()->first()->setMerchantSectorStatus(
                $this->entityManager->find(MerchantSectorStatus::class, MerchantSectorStatus::STATUS_TYPE_PASSIVE_ID)
            );
            $merchant->getMerchantSectors()->first()->setIsActive(false);
            $merchant->setIsActive(false);
            $merchant->setPhoneNumber(PhoneNumberUtil::fixPhoneNumber($merchant->getPhoneNumber()));
            $merchant->setLandPhoneNumber(PhoneNumberUtil::fixPhoneNumber($merchant->getLandPhoneNumber()));
            $merchant->setFaxNumber(PhoneNumberUtil::fixPhoneNumber($merchant->getFaxNumber()));

            if (!empty(str_replace(' ', '', $merchant->getIban()))) {
                $merchant->setIban(str_replace(' ', '', $merchant->getIban()));
            }

            if (empty($merchant->getTaxNumber())) {
                $merchant->setTaxNumber($user->getNationalId());
            }

            $user->setMerchant($merchant);
            $user->setDefaultSector($merchant->getMerchantSectors()->first()->getSector());
            $user->setMerchantRoles([UserVoter::ROLE_OWNER]);
            $user->setRoles([UserVoter::ROLE_USER]);
            $user->setIsActive(false);
            $user->setEmail($merchant->getEmail());
            $user->setValidationCode(Generate::key());
            $user->setIpAddress($request->getClientIp());
            $user->setPhoneNumber(PhoneNumberUtil::fixPhoneNumber($merchant->getPhoneNumber()));
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            if (empty($user->getSmsPermission())) {
                $user->setSmsPermission(false);
            }

            if (empty($user->getEmailPermission())) {
                $user->setEmailPermission(false);
            }

            $address->setUser($user);
            $address->setMerchant($merchant);
            $address->setTitle('Fatura Adresi');
            $address->setAddressType(
                $this->entityManager->getReference(
                    AddressType::class,
                    AddressType::BILLING_ID
                )
            );
            $address->setContactName(sprintf('%s %s', $user->getFirstName(), $user->getLastName()));
            $address->setPhoneNumber($merchant->getPhoneNumber());

            $this->entityManager->persist($merchant);
            $this->entityManager->persist($user);
            $this->entityManager->persist($address);

            $this->entityManager->flush();

            if ($merchant instanceof Merchant) {
                $this->container->get('event_dispatcher')->dispatch(new MerchantRegisteredEvent($user), MerchantRegisteredEvent::NAME);
            }

            return $merchant;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AuthService][register] %s', $e), [
                'shopName' => $merchant->getShopName(),
                'email' => $merchant->getEmail(),
                'phoneNumber' => $merchant->getPhoneNumber(),

            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AuthService][register] %s', $e), [
                'shopName' => $merchant->getShopName(),
                'email' => $merchant->getEmail(),
                'phoneNumber' => $merchant->getPhoneNumber(),
            ]);
        }

        return null;
    }
}
