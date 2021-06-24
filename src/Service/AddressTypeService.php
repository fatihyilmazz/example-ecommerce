<?php

namespace App\Service;

use App\Entity\AddressType;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddressTypeService extends AbstractService
{
    /**
     * @var \App\Repository\AddressTypeRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $addressTypeRepository;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        parent::__construct($container, $logger);

        $this->addressTypeRepository = $this->entityManager->getRepository(AddressType::class);
    }

    /**
     * @return AddressType[]|AddressType
     */
    public function getAll()
    {
        return $this->addressTypeRepository->findAll();
    }

    /**
     * @param int $id
     *
     * @return AddressType|object|null
     */
    public function findById(int $id)
    {
        return $this->addressTypeRepository->find($id);
    }

    /**
     * @return AddressType[]|array|object[]
     */
    public function getAddressTypesWithoutBilling()
    {
        try {
            return $this->addressTypeRepository->createQueryBuilder('at')
                ->where('at.id != :billingTypeId')
                ->setParameter('billingTypeId', AddressType::BILLING_ID)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[AddressTypeService][getAddressTypesWithoutBilling] %s', $e));
        } catch (\Error $e) {
            $this->logger->error(sprintf('[AddressTypeService][getAddressTypesWithoutBilling] %s', $e));

        }

        return null;
    }
}
