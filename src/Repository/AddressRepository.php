<?php

namespace App\Repository;

use App\Entity\Merchant;
use App\Entity\Address;
use App\Entity\AddressType;
use App\Security\Voter\UserVoter;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Address|null find($id, $lockMode = null, $lockVersion = null)
 * @method Address|null findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /**
     * @param Merchant $merchant
     * @return null|Address
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBillingAddressByMerchant(Merchant $merchant)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.user', 'u')
            ->innerJoin('u.merchant', 'm')
            ->where('m.id = :merchantId')
            ->andWhere('u.merchantRoles LIKE :roles')
            ->andWhere('a.addressType = :addressType')
            ->setParameters([
                'merchantId' => $merchant->getId(),
                'addressType' => AddressType::BILLING_ID,
                'roles' => '%"'.UserVoter::ROLE_OWNER.'"%',
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Address
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
