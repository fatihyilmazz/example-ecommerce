<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Banner;
use App\Entity\Category;
use App\Entity\Merchant;
use App\Entity\Notification;
use App\Entity\ProductComment;
use App\Entity\MerchantReview;
use App\Service\ReportService;
use App\Entity\MerchantHistory;
use App\Entity\MerchantContact;
use App\Entity\MainPageProduct;
use App\Entity\WishListProduct;
use App\Entity\DefectiveProduct;
use Doctrine\Common\Cache\Cache;
use App\Event\CacheInvalidatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MerchantPerformanceQuestionService;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CacheInvalidationSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var TagAwareCacheInterface
     */
    protected $cacheProvider;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TagAwareCacheInterface $cacheProvider
     */
    public function __construct(EntityManagerInterface $entityManager, TagAwareCacheInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CacheInvalidatedEvent::NAME_BANNER_UPDATED => 'onBannerUpdatedEvent',
            CacheInvalidatedEvent::NAME_MAIN_PAGE_PRODUCTS_UPDATED => 'onMainPageProductUpdatedEvent',
            CacheInvalidatedEvent::NAME_NOTIFICATION_UPDATED => 'onNotificationUpdatedEvent',
            CacheInvalidatedEvent::NAME_USER_UPDATED => 'onUserUpdatedEvent',
            CacheInvalidatedEvent::NAME_CATEGORY_UPDATED => 'onCategoryUpdatedEvent',
            CacheInvalidatedEvent::NAME_PRODUCT_COMMENT_UPDATED => 'onProductCommentUpdatedEvent',
            CacheInvalidatedEvent::NAME_PRODUCT_WISH_LIST_PRODUCT_UPDATED => 'onWishListProductUpdatedEvent',
            CacheInvalidatedEvent::NAME_MERCHANT_REVIEW_UPDATED => 'onMerchantReviewUpdatedEvent',
            CacheInvalidatedEvent::NAME_MERCHANT_UPDATED => 'onMerchantUpdatedEvent',
            CacheInvalidatedEvent::NAME_ORDER_UPDATED => 'onOrderUpdatedEvent',
            CacheInvalidatedEvent::NAME_MERCHANT_HISTORY_UPDATED => 'onMerchantHistoriesUpdatedEvent',
            CacheInvalidatedEvent::NAME_MERCHANT_CONTACT_UPDATED => 'onMerchantContactUpdatedEvent',
            CacheInvalidatedEvent::NAME_DEFECTIVE_PRODUCT_UPDATED => 'onDefectiveProductUpdatedEvent',
        ];
    }

    /**
     * @param CacheInvalidatedEvent $event
     */
    public function onMainPageProductUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof MainPageProduct)) {
            $cacheDriver->delete(MainPageProduct::CACHE_KEY_ALL);
            $cacheDriver->delete(sprintf('%s%s', MainPageProduct::CACHE_KEY_BY_TYPE_ID, $event->getEntity()->getMainPageProductType()->getId()));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     */
    public function onBannerUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof Banner)) {
            $cacheKey = sprintf('%s%s', Banner::CACHE_KEY_BY_SECTOR, $event->getEntity()->getSector()->getId());

            $cacheDriver->delete($cacheKey);
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onNotificationUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof Notification)) {
            $cacheDriver->delete(Notification::CACHE_KEY_ALL);

            $this->cacheProvider->invalidateTags([
                Notification::CACHE_TAG_ALL
            ]);
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onUserUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof User)) {
            $cacheDriver->delete(User::CACHE_KEY_IDS_AND_NAMES);

            $this->cacheProvider->invalidateTags([
                User::CACHE_TAG,
            ]);
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onMerchantUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof Merchant)) {
            $cacheDriver->delete(Merchant::CACHE_KEY_IDS_AND_NAMES);
            $cacheDriver->delete(sprintf('%s%s', Merchant::CACHE_KEY_BY_ID, $event->getEntity()->getId()));
            $cacheDriver->delete(Merchant::CACHE_KEY_DELETED_MERCHANT_IDS);
            $cacheDriver->delete(Merchant::CACHE_KEY_INACTIVE_MERCHANT_IDS);
            $cacheDriver->delete(Merchant::CACHE_KEY_CLOSED_MARKETPLACE_MERCHANT_IDS);

            $cacheDriver->delete(Merchant::CACHE_KEY_NUMBER_OF_ACTIVE_MERCHANTS);
            $cacheDriver->delete(Merchant::CACHE_KEY_NUMBER_OF_PASSIVE_MERCHANTS);

            $cacheDriver->delete(Merchant::CACHE_KEY_NUMBER_OF_ACTIVE_SELLER_MERCHANTS);
            $cacheDriver->delete(Merchant::CACHE_KEY_NUMBER_OF_PASSIVE_SELLER_MERCHANTS);

            $this->cacheProvider->invalidateTags([
                Merchant::CACHE_TAG,
            ]);
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onCategoryUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof Category)) {
            $cacheDriver->delete(sprintf('%s%s', Category::CACHE_KEY_BY_SECTOR_ID, $event->getEntity()->getSector()->getId()));

            $this->cacheProvider->delete(sprintf(
                '%s%s',
                Category::CACHE_KEY_BY_SLUG,
                md5($event->getEntity()->getSlug())
            ));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     */
    public function onProductCommentUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof ProductComment)) {
            $cacheDriver->delete(sprintf('%sp%s-m%s', ProductComment::CACHE_KEY_COMMENT, $event->getEntity()->getProductId(), $event->getEntity()->getMerchant()->getId()));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     */
    public function onWishListProductUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof WishListProduct)) {
            $cacheDriver->delete(sprintf(
                '%s-u%s-s%s-p%s-m%s',
                WishListProduct::CACHE_KEY_WISH_LIST_PRODUCT,
                $event->getEntity()->getUser()->getId(),
                $event->getEntity()->getSector()->getId(),
                $event->getEntity()->getProductId(),
                $event->getEntity()->getMerchant()->getId()
            ));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onMerchantReviewUpdatedEvent(CacheInvalidatedEvent $event)
    {
        if (($event->getEntity() instanceof MerchantReview)) {
            $this->cacheProvider->delete(sprintf(
                '%s-%s-%s',
                MerchantPerformanceQuestionService::CACHE_KEY_PERFORMANCE_MERCHANT_SECTOR,
                $event->getEntity()->getMerchant()->getId(),
                $event->getEntity()->getSector()->getId()
            ));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onOrderUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof Order)) {
            $this->cacheProvider->invalidateTags(
                [
                    Order::CACHE_TAG_ORDER_ALL,
                ]
            );
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     */
    public function onMerchantHistoriesUpdatedEvent(CacheInvalidatedEvent $event)
    {
        $cacheDriver = $this->entityManager->getConfiguration()->getQueryCacheImpl();
        if (($cacheDriver instanceof Cache) && ($event->getEntity() instanceof MerchantHistory)) {
            $cacheDriver->delete(sprintf(
                '%s%s',
                ReportService::numberOfMonthlyLastOrders,
                MerchantHistory::CACHE_KEY_X_MONTHLY_HISTORIES
            ));
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onMerchantContactUpdatedEvent(CacheInvalidatedEvent $event)
    {
        if (($event->getEntity() instanceof MerchantContact)) {
            $this->cacheProvider->invalidateTags([
                MerchantContact::CACHE_TAG,
            ]);
        }
    }

    /**
     * @param CacheInvalidatedEvent $event
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function onDefectiveProductUpdatedEvent(CacheInvalidatedEvent $event)
    {
        if (($event->getEntity() instanceof DefectiveProduct)) {
            $this->cacheProvider->invalidateTags([
                DefectiveProduct::CACHE_TAG,
            ]);
        }
    }
}
