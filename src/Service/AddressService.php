<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\User;
use App\Entity\County;
use App\Entity\Basket;
use App\Entity\Address;
use App\Entity\Merchant;
use App\Entity\AddressType;
use Psr\Log\LoggerInterface;
use App\Utils\PhoneNumberUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\PaymentManagement\MerchantService as PMPMerchantService;

class AddressService extends AbstractService
{
    /**
     * @var \App\Repository\AddressRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $addressRepository;

    /**
     * @var \App\Repository\CityRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $cityRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $countyRepository;

    /**
     * @var PMPMerchantService
     */
    protected $pmpMerchantService;

    /**
     * AddressService constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param PMPMerchantService $pmpMerchantService
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, PMPMerchantService $pmpMerchantService)
    {
        parent::__construct($container, $logger);

        $this->addressRepository = $this->entityManager->getRepository(Address::class);
        $this->cityRepository = $this->entityManager->getRepository(City::class);
        $this->countyRepository = $this->entityManager->getRepository(County::class);
        $this->pmpMerchantService = $pmpMerchantService;
    }

    /**
     * @param Address $address
     *
     * @return Address|null
     */
    public function create(Address $address)
    {
        try {
            $address->setPhoneNumber(PhoneNumberUtil::fixPhoneNumber($address->getPhoneNumber()));

            $this->entityManager->persist($address);
            $this->entityManager->flush($address);

            return $address;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][create] %s', $e), [
                'addressId' => $address->getUser()->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][create] %s', $e), [
                'addressId' => $address->getUser()->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Address $address
     *
     * @return Address|null
     */
    public function update(Address $address)
    {
        try {
            $address->setUpdatedAt(new \DateTime());
            $address->setPhoneNumber(PhoneNumberUtil::fixPhoneNumber($address->getPhoneNumber()));

            $this->entityManager->persist($address);
            $this->entityManager->flush($address);

            if ($address->getAddressType()->getId() == AddressType::BILLING_ID) {
                $this->pmpMerchantService->updateMerchant($address->getMerchant(), $address);
            }

            return $address;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][update] %s', $e), [
                'addressId' => $address->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][update] %s', $e), [
                'addressId' => $address->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Address $address
     *
     * @return bool
     */
    public function delete(Address $address)
    {
        try {
            $address->setDeletedAt(new \DateTime());

            $this->entityManager->flush($address);

            return true;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][delete] %s', $e), [
                'addressId' => $address->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][delete] %s', $e), [
                'addressId' => $address->getId(),
            ]);
        }

        return false;
    }

    /**
     * @return City[]|array|object[]
     */
    public function getAllCities()
    {
        return $this->cityRepository->findAll();
    }

    /**
     * @param City $city
     *
     * @return County|County[]|null
     */
    public function getCountyOfCity(City $city)
    {
        try {
            return $this->countyRepository->createQueryBuilder('county')
                ->select('county')
                ->where('county.city = :cityId')
                ->orderBy('county.name', 'ASC')
                ->setParameter(':cityId', $city->getId())
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][getCountyOfCity] %s', $e), [
                'cityId' => $city->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error('[AddressService][getCountyOfCity]', [
                'cityId' => $city->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Merchant $merchant
     *
     * @return Address|Address[]|null
     */
    public function getAddressesOfMerchant(Merchant $merchant)
    {
        try {
            return $this->addressRepository->createQueryBuilder('a')
                ->select('a')
                ->where('a.deletedAt IS NULL')
                ->andWhere('a.merchant = :merchantId')
                ->orderBy('a.id', 'DESC')
                ->setParameter(':merchantId', $merchant->getId())
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][getAddressesOfMerchant] %s', $e), [
                'merchantId' => $merchant->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][getAddressesOfMerchant] %s', $e), [
                'merchantId' => $merchant->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|null
     */
    public function addAddressesToBasket(Basket $basket)
    {
        try {
            $this->entityManager->persist($basket);
            $this->entityManager->flush($basket);

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][addAddressesToBasket] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][addAddressesToBasket] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Merchant $merchant
     *
     * @return Address|null
     */
    public function getBillingAddressByMerchant(Merchant $merchant)
    {
        try {
            return $this->addressRepository->getBillingAddressByMerchant($merchant);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][getBillingAddressByMerchant] %s', $e), [
                'merchantId' => $merchant->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][getBillingAddressByMerchant] %s', $e), [
                'merchantId' => $merchant->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return Address|null
     */
    public function getBillingAddressByUser(User $user)
    {
        try {
            return $this->getBillingAddressByMerchant($user->getMerchant());
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressService][getBillingAddressByUser] %s', $e), [
                'userId' => $user->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressService][getBillingAddressByUser] %s', $e), [
                'userId' => $user->getId(),
            ]);
        }

        return null;
    }
}
