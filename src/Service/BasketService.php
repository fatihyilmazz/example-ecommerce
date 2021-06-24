<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Basket;
use App\Entity\Currency;
use App\Utils\CargoUtil;
use App\Utils\MoneyUtil;
use App\Entity\Merchant;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;
use App\Entity\CargoCompany;
use App\Entity\BasketProduct;
use App\DTO\BasketSummaryDTO;
use App\Entity\Custom\Campaign;
use Doctrine\ORM\Query\Expr\Join;
use App\Event\HasConflictProductEvent;
use App\Entity\FilterClass\BasketFilter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\ProductManagement\Request\Product\ProductFilter;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class BasketService extends AbstractService
{
    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var CargoService
     */
    protected $cargoService;

    /**
     * @var \App\Repository\BasketRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $basketRepository;

    /**
     * @var \App\Repository\BasketProductRepository|\Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected $basketProductRepository;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param ProductService $productService
     * @param CargoService $cargoService
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        ProductService $productService,
        CargoService $cargoService
    ) {
        parent::__construct($container, $logger);

        $this->basketRepository = $this->entityManager->getRepository(Basket::class);
        $this->basketProductRepository = $this->entityManager->getRepository(BasketProduct::class);

        $this->productService = $productService;
        $this->cargoService = $cargoService;
    }

    /**
     * @param User $user
     *
     * @return Basket
     */
    public function createBasket(User $user): Basket
    {
        try {
            $basket = $this->getCurrentBasketByUser($user);

            if ($basket instanceof Basket) {
                return $basket;
            }

            $basket = new Basket();
            $basket->setUser($user);
            $basket->setSector($user->getDefaultSector());

            $this->entityManager->persist($basket);
            $this->entityManager->flush($basket);

            $user->addBasket($basket);

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][createBasket] %s', $e), [
                'userId' => $user->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][createBasket] %s', $e), [
                'userId' => $user->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return BasketProduct|null
     */
    public function addBasketProduct(BasketProduct $basketProduct)
    {
        try {
            $availableBasketProduct = $this->basketProductRepository->findOneBy([
                'merchant' => $basketProduct->getMerchant(),
                'basket' => $basketProduct->getBasket(),
                'productId' => $basketProduct->getProductId(),
            ]);

            if ($availableBasketProduct instanceof BasketProduct) {
                $availableBasketProduct->setQuantity($basketProduct->getQuantity());

                $this->entityManager->persist($availableBasketProduct);
            } else {
                $this->entityManager->persist($basketProduct);
            }

            $this->entityManager->flush();

            return $basketProduct;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][addBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
                'basketId' => $basketProduct->getBasket()->getId(),
                'merchantId' => $basketProduct->getMerchant()->getId(),
                'productId' => $basketProduct->getProductId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][addBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
                'basketId' => $basketProduct->getBasket()->getId(),
                'merchantId' => $basketProduct->getMerchant()->getId(),
                'productId' => $basketProduct->getProductId(),
            ]);
        }

        return null;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return BasketProduct|object|null
     */
    public function findBasketProduct(BasketProduct $basketProduct)
    {
        try {
            return $this->basketProductRepository->findOneBy([
                'merchant' => $basketProduct->getMerchant(),
                'basket' => $basketProduct->getBasket(),
                'productId' => $basketProduct->getProductId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][findBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][findBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return BasketProduct|null
     */
    public function increaseOrCreateBasketProduct(BasketProduct $basketProduct)
    {
        //TODO bulkIncreaseBasketProductQuantity içnide kullanıyor kontrol et sil

        try {
            $availableBasketProduct = $this->basketProductRepository->findOneBy([
                'merchant' => $basketProduct->getMerchant(),
                'basket' => $basketProduct->getBasket(),
                'productId' => $basketProduct->getProductId(),
            ]);

            if ($availableBasketProduct instanceof BasketProduct) {
                $availableBasketProduct->setQuantity(
                    $availableBasketProduct->getQuantity() + $basketProduct->getQuantity()
                );

                $this->entityManager->persist($availableBasketProduct);
            } else {
                $this->entityManager->persist($basketProduct);
            }

            $this->entityManager->flush();

            $this->entityManager->refresh($basketProduct->getBasket());

            return $basketProduct;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][increaseOrCreateBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][increaseOrCreateBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return BasketProduct|null
     */
    public function saveBasketProduct(BasketProduct $basketProduct)
    {
        try {
            $this->entityManager->persist($basketProduct);
            $this->entityManager->flush($basketProduct);

            $this->entityManager->refresh($basketProduct->getBasket());

            return $basketProduct;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][saveBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][saveBasketProduct] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|null
     */
    public function getBasketSummary(Basket $basket)
    {
        try {
            //TODO Coupons
            $basket = $this->prepareBasketProducts($basket);

            $basket = $this->prepareBasketSummary($basket);

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][getBasketSummary] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][getBasketSummary] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return bool
     */
    public function hardDelete(BasketProduct $basketProduct): bool
    {
        try {
            $this->entityManager->remove($basketProduct);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][hardDelete] %s', $e), [
                'basketProductId' => $basketProduct->getId(),

            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][hardDelete] %s', $e), [
                'basketProductId' => $basketProduct->getId(),
            ]);
        }

        return false;
    }

    /**
     * @param array $product
     * @param BasketProduct $basketProduct
     *
     * @return array
     */
    public function decideToCampaignQuantityForBasket(array $product, BasketProduct $basketProduct): array
    {
        $defaultQuantity = [
            'quantityWithoutCampaign' => $basketProduct->getQuantity(),
            'quantityWithCampaign' => 0
        ];

        try {
            if ($product['campaignDetail']['usedCampaign']['min_quantity'] == $product['campaignDetail']['usedCampaign']['max_quantity']) {
                $quantityWithoutCampaign = $basketProduct->getQuantity() - $product['campaignDetail']['usedCampaign']['max_quantity'];
                $quantityWithCampaign = $product['campaignDetail']['usedCampaign']['max_quantity'];
            } elseif (($basketProduct->getQuantity() >= $product['campaignDetail']['usedCampaign']['min_quantity'] &&
                    $basketProduct->getQuantity() <= $product['campaignDetail']['usedCampaign']['max_quantity']) ||
                empty($product['campaignDetail']['usedCampaign']['max_quantity'])) {
                $quantityWithoutCampaign = 0;
                $quantityWithCampaign = $basketProduct->getQuantity();
            } else {
                $quantityWithoutCampaign = $basketProduct->getQuantity() % $product['campaignDetail']['usedCampaign']['max_quantity'];
                $quantityWithCampaign = $basketProduct->getQuantity() - $quantityWithoutCampaign;
            }

            $quantityResult = [
                'quantityWithoutCampaign' => $quantityWithoutCampaign,
                'quantityWithCampaign' => $quantityWithCampaign
            ];

            if (($quantityResult['quantityWithoutCampaign'] + $quantityResult['quantityWithCampaign']) == $basketProduct->getQuantity()) {
                return $quantityResult;
            } else {
                return $defaultQuantity;
            }
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][decideToCampaignQuantityForBasket] %s', $e), [
                'product' => $product['id'],
                'basketProduct' => $basketProduct->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][decideToCampaignQuantityForBasket] %s', $e), [
                'product' => $product['id'],
                'basketProduct' => $basketProduct->getId(),
            ]);
        }

        return $defaultQuantity;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket
     */
    protected function prepareBasketProducts(Basket $basket)
    {
        try {
            $basket->getBasketProducts()->filter(function (BasketProduct $basketProduct) use ($basket) {
                $productFilter = new ProductFilter();
                $productFilter->setSectorId($basket->getSector()->getProductManagementSectorId());
                $productFilter->setProductId($basketProduct->getProductId());
                $productFilter->setMerchantId($basketProduct->getMerchant()->getId());
                $productFilter->setBuyerMerchantId($basket->getUser()->getMerchant()->getId());
                $productFilter->setBuyerUserId($basket->getUser()->getId());
                $productFilter->setSegmentId($basket->getUser()->getMerchant()->getSegmentId());
                $productFilter->setCurrentGroupId($basket->getUser()->getMerchant()->getCurrentGroupId());

                $productSearchRequest = new ProductSearchRequest();
                $productSearchRequest->setFilter($productFilter);
                $productSearchRequest->setIncludes(['medias', 'categories', 'platforms', 'segmentPrices', 'specs', 'campaigns']);

                $product = $this->productService->getProductById($productSearchRequest, $basketProduct, false);

                if (empty($product)) {
                    $this->logger->alert('[BasketService][prepareBasketProducts]Product removed from the basket because of empty value.', [
                        'basketId' => $basketProduct->getBasket()->getId(),
                        'merchantId' => $basketProduct->getMerchant()->getId(),
                        'productId' => $basketProduct->getProductId(),
                    ]);

                    $basket->removeBasketProduct($basketProduct);

                    $this->entityManager->persist($basket);
                    $this->entityManager->flush($basket);

                    return;
                }

                if ($basketProduct->getQuantity() > $product['quantity']) {
                    $this->logger->info('[BasketService][prepareBasketProducts]Product quantity lower than basket product quantity', [
                        'basketId' => $basketProduct->getBasket()->getId(),
                        'merchantId' => $basketProduct->getMerchant()->getId(),
                        'productId' => $basketProduct->getProductId(),
                        'basketProductQuantity' => $basketProduct->getQuantity(),
                        'productQuantity' => $product['quantity']
                    ]);

                    $basket->removeBasketProduct($basketProduct);
                    $this->entityManager->persist($basket);
                    $this->entityManager->flush($basket);

                    $basket->addConflictProduct([
                        'basketProduct' => [
                            'merchantId' => $basketProduct->getMerchant()->getId(),
                            'merchantName' => $basketProduct->getMerchant()->getShopName(),
                            'quantity' => $basketProduct->getQuantity(),
                        ],
                        'product' => $product,
                    ]);
                } else {
                    $basketProduct->setProduct($product);
                    if (!empty($product['campaignDetail']['usedCampaign'])) {
                        $exchangeService = $this->container->get(ExchangeService::class);

                        $quantityResult = $this->decideToCampaignQuantityForBasket($product, $basketProduct);
                        $quantityWithoutCampaign = $quantityResult['quantityWithoutCampaign'];
                        $quantityWithCampaign = $quantityResult['quantityWithCampaign'];

                        $campaign = new Campaign();
                        $campaign->setId($product['campaignDetail']['usedCampaign']['id']);
                        $campaign->setTitle($product['campaignDetail']['usedCampaign']['title']);
                        $campaign->setDiscountTypeId($product['campaignDetail']['usedCampaign']['discount_type_id']);
                        $campaign->setDiscount($product['campaignDetail']['usedCampaign']['discount']);
                        $campaign->setMinQuantity($product['campaignDetail']['usedCampaign']['min_quantity']);
                        $campaign->setMaxQuantity($product['campaignDetail']['usedCampaign']['max_quantity']);

                        $rates = $exchangeService->getRates();
                        $usdExchangeRate = $rates['usd']['rate'];
                        $eurExchangeRate = $rates['eur']['rate'];
                        if ($product['currencyId'] == Currency::ID_EUR) {
                            $exchangeRate = $eurExchangeRate;
                        } elseif ($product['currencyId'] == Currency::ID_USD) {
                            $exchangeRate = $usdExchangeRate;
                        } else {
                            $this->logger->critical('Invalid currency. As default used USD exchange rate.', [
                                'product' => $product,
                            ]);
                            $exchangeRate = $usdExchangeRate;
                        }

                        $taxRate = $product['tax_rate'];

                        /** Without Campaign */
                        $productPrice = MoneyUtil::formatPrice($product['priceWithoutDiscount']);

                        $productKDV = MoneyUtil::formatPrice(($taxRate * $productPrice));
                        $productUnitPrice = $productKDV + $productPrice;

                        $campaign->setQuantity($quantityWithoutCampaign);
                        $campaign->setUnitPriceWithoutTax($productPrice);
                        $campaign->setUnitPrice($productUnitPrice);
                        $campaign->setMerchantPrice(MoneyUtil::formatPrice($product['merchantPrice']));

                        if ($campaign->getDiscountTypeId() == CampaignService::PERCENTAGE_DISCOUNT) {
                            $campaign->setDiscountAmount($campaign->getMerchantPrice() - (($campaign->getMerchantPrice() * $product['campaignDetail']['usedCampaign']['discount']) / 100));
                        } elseif ($campaign->getDiscountTypeId() == CampaignService::AMOUNT_DISCOUNT) {
                            $campaign->setDiscountAmount($product['campaignDetail']['usedCampaign']['discount']);
                        }

                        $productPriceTL = MoneyUtil::formatPrice($productPrice * $exchangeRate);
                        $productKDVTL = MoneyUtil::formatPrice(($taxRate * $productPriceTL));
                        $productUnitPriceTL = $productKDVTL + $productPriceTL;

                        $campaign->setUnitPriceWithoutTaxTL($productPriceTL);
                        $campaign->setUnitPriceTL($productUnitPriceTL);
                        $campaign->setDiscountAmountTL(MoneyUtil::formatPrice($campaign->getDiscountAmount() * $exchangeRate));

                        $campaign->setTotalPriceWithoutTax($campaign->getQuantity() * $campaign->getUnitPriceWithoutTax());
                        $campaign->setTotalPrice($campaign->getQuantity() * $campaign->getUnitPrice());
                        $campaign->setTotalTax($campaign->getTotalPrice() - $campaign->getTotalPriceWithoutTax());

                        $campaign->setTotalPriceWithoutTaxTL($campaign->getQuantity() * $campaign->getUnitPriceWithoutTaxTL());
                        $campaign->setTotalPriceTL($campaign->getQuantity() * $campaign->getUnitPriceTL());
                        $campaign->setTotalTaxTL($campaign->getTotalPriceTL() - $campaign->getTotalPriceWithoutTaxTL());

                        /** With Campaign */
                        $productPrice = MoneyUtil::formatPrice($product['price']);

                        $productKDV = MoneyUtil::formatPrice(($taxRate * $productPrice));
                        $productUnitPrice = $productKDV + $productPrice;

                        $campaign->setQuantityForCampaign($quantityWithCampaign);
                        $campaign->setMerchantPrice(MoneyUtil::formatPrice($product['merchantPrice']));
                        $campaign->setUnitPriceWithoutTaxForCampaign($productPrice);
                        $campaign->setUnitPriceForCampaign($productUnitPrice);

                        $productPriceTL = MoneyUtil::formatPrice($productPrice * $exchangeRate);
                        $productKDVTL = MoneyUtil::formatPrice(($taxRate * $productPriceTL));
                        $productUnitPriceTL = $productKDVTL + $productPriceTL;

                        $campaign->setUnitPriceWithoutTaxForCampaignTL($productPriceTL);
                        $campaign->setUnitPriceForCampaignTL($productUnitPriceTL);


                        $campaign->setTotalPriceWithoutTaxForCampaign($campaign->getQuantityForCampaign() * $campaign->getUnitPriceWithoutTaxForCampaign());
                        $campaign->setTotalPriceForCampaign($campaign->getQuantityForCampaign() * $campaign->getUnitPriceForCampaign());
                        $campaign->setTotalTaxForCampaign($campaign->getTotalPriceForCampaign() - $campaign->getTotalPriceWithoutTaxForCampaign());

                        $campaign->setTotalPriceWithoutTaxForCampaignTL($campaign->getQuantityForCampaign() * $campaign->getUnitPriceWithoutTaxForCampaignTL());
                        $campaign->setTotalPriceForCampaignTL($campaign->getQuantityForCampaign() * $campaign->getUnitPriceForCampaignTL());
                        $campaign->setTotalTaxForCampaignTL($campaign->getTotalPriceForCampaignTL() - $campaign->getTotalPriceWithoutTaxForCampaignTL());

                        /** Total */
                        $campaign->setGrandSubTotal($campaign->getTotalPriceWithoutTaxForCampaign() + $campaign->getTotalPriceWithoutTax());
                        $campaign->setGrandTotalTax($campaign->getTotalTaxForCampaign() + $campaign->getTotalTax());
                        $campaign->setGrandTotal($campaign->getTotalPriceForCampaign() + $campaign->getTotalPrice());
                        $campaign->setGrandTotalDiscountAmount(($basketProduct->getQuantity() * $campaign->getUnitPriceWithoutTax()) - $campaign->getGrandSubTotal());

                        $campaign->setGrandSubTotalTL($campaign->getTotalPriceWithoutTaxForCampaignTL() + $campaign->getTotalPriceWithoutTaxTL());
                        $campaign->setGrandTotalTaxTL($campaign->getTotalTaxForCampaignTL() + $campaign->getTotalTaxTL());
                        $campaign->setGrandTotalTL($campaign->getTotalPriceForCampaignTL() + $campaign->getTotalPriceTL());
                        $campaign->setGrandTotalDiscountAmountTL(MoneyUtil::formatPrice($campaign->getGrandTotalDiscountAmount() * $exchangeRate));

                        $basketProduct->setCampaign($campaign);
                    }
                }

                return $basketProduct;
            });

            if ($basket->hasConflictProduct()) {
                $this->container->get('event_dispatcher')->dispatch(
                    new HasConflictProductEvent($basket),
                    HasConflictProductEvent::NAME
                );
            }

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][prepareBasketProducts] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][prepareBasketProducts] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|null
     */
    protected function prepareBasketProductsWithoutQuantityControl(Basket $basket)
    {
        try {
            $basket->getBasketProducts()->filter(function (BasketProduct $basketProduct) use ($basket) {
                $productFilter = new ProductFilter();
                $productFilter->setSectorId($basket->getSector()->getId());
                $productFilter->setProductId($basketProduct->getProductId());
                $productFilter->setMerchantId($basketProduct->getMerchant()->getId());
                $productFilter->setBuyerMerchantId($basket->getUser()->getMerchant()->getId());
                $productFilter->setBuyerUserId($basket->getUser()->getId());
                $productFilter->setSegmentId($basket->getUser()->getMerchant()->getSegmentId());
                $productFilter->setCurrentGroupId($basket->getUser()->getMerchant()->getCurrentGroupId());

                $productSearchRequest = new ProductSearchRequest();
                $productSearchRequest->setFilter($productFilter);
                $productSearchRequest->setIncludes(['medias', 'platforms', 'segmentPrices', 'campaigns']);

                $product = $this->productService->getProductById($productSearchRequest, $basketProduct, false);

                if (empty($product)) {
                    $this->logger->alert('[BasketService][prepareBasketProductsWithoutQuantityControl]Product removed from the basket because of empty value.', [
                        'basketId' => $basketProduct->getBasket()->getId(),
                        'merchantId' => $basketProduct->getMerchant()->getId(),
                        'productId' => $basketProduct->getProductId(),
                    ]);

                    $basket->removeBasketProduct($basketProduct);

                    return;
                }

                $basketProduct->setProduct($product);
                if (!empty($product['campaignDetail']['usedCampaign'])) {
                    $exchangeService = $this->container->get(ExchangeService::class);

                    $quantityResult = $this->decideToCampaignQuantityForBasket($product, $basketProduct);
                    $quantityWithoutCampaign = $quantityResult['quantityWithoutCampaign'];
                    $quantityWithCampaign = $quantityResult['quantityWithCampaign'];

                    $campaign = new Campaign();
                    $campaign->setId($product['campaignDetail']['usedCampaign']['id']);
                    $campaign->setTitle($product['campaignDetail']['usedCampaign']['title']);
                    $campaign->setDiscountTypeId($product['campaignDetail']['usedCampaign']['discount_type_id']);
                    $campaign->setDiscount($product['campaignDetail']['usedCampaign']['discount']);
                    $campaign->setMinQuantity($product['campaignDetail']['usedCampaign']['min_quantity']);
                    $campaign->setMaxQuantity($product['campaignDetail']['usedCampaign']['max_quantity']);

                    $rates = $exchangeService->getRates();
                    $usdExchangeRate = $rates['usd']['rate'];
                    $eurExchangeRate = $rates['eur']['rate'];
                    if ($product['currencyId'] == Currency::ID_EUR) {
                        $exchangeRate = $eurExchangeRate;
                    } elseif ($product['currencyId'] == Currency::ID_USD) {
                        $exchangeRate = $usdExchangeRate;
                    } else {
                        $this->logger->critical('Invalid currency. As default used USD exchange rate.', [
                            'product' => $product,
                        ]);
                        $exchangeRate = $usdExchangeRate;
                    }

                    $taxRate = $product['tax_rate'];

                    /** Without Campaign */
                    $productPrice = MoneyUtil::formatPrice($product['priceWithoutDiscount']);

                    $productKDV = MoneyUtil::formatPrice(($taxRate * $productPrice));
                    $productUnitPrice = $productKDV + $productPrice;

                    $campaign->setQuantity($quantityWithoutCampaign);
                    $campaign->setUnitPriceWithoutTax($productPrice);
                    $campaign->setUnitPrice($productUnitPrice);
                    $campaign->setMerchantPrice(MoneyUtil::formatPrice($product['merchantPrice']));

                    if ($campaign->getDiscountTypeId() == CampaignService::PERCENTAGE_DISCOUNT) {
                        $campaign->setDiscountAmount($campaign->getMerchantPrice() - (($campaign->getMerchantPrice() * $product['campaignDetail']['usedCampaign']['discount']) / 100));
                    } elseif ($campaign->getDiscountTypeId() == CampaignService::AMOUNT_DISCOUNT) {
                        $campaign->setDiscountAmount($product['campaignDetail']['usedCampaign']['discount']);
                    }

                    $productPriceTL = MoneyUtil::formatPrice($productPrice * $exchangeRate);
                    $productKDVTL = MoneyUtil::formatPrice(($taxRate * $productPriceTL));
                    $productUnitPriceTL = $productKDVTL + $productPriceTL;

                    $campaign->setUnitPriceWithoutTaxTL($productPriceTL);
                    $campaign->setUnitPriceTL($productUnitPriceTL);
                    $campaign->setDiscountAmountTL(MoneyUtil::formatPrice($campaign->getDiscountAmount() * $exchangeRate));

                    $campaign->setTotalPriceWithoutTax($campaign->getQuantity() * $campaign->getUnitPriceWithoutTax());
                    $campaign->setTotalPrice($campaign->getQuantity() * $campaign->getUnitPrice());
                    $campaign->setTotalTax($campaign->getTotalPrice() - $campaign->getTotalPriceWithoutTax());

                    $campaign->setTotalPriceWithoutTaxTL($campaign->getQuantity() * $campaign->getUnitPriceWithoutTaxTL());
                    $campaign->setTotalPriceTL($campaign->getQuantity() * $campaign->getUnitPriceTL());
                    $campaign->setTotalTaxTL($campaign->getTotalPriceTL() - $campaign->getTotalPriceWithoutTaxTL());

                    /** With Campaign */
                    $productPrice = MoneyUtil::formatPrice($product['price']);

                    $productKDV = MoneyUtil::formatPrice(($taxRate * $productPrice));
                    $productUnitPrice = $productKDV + $productPrice;

                    $campaign->setQuantityForCampaign($quantityWithCampaign);
                    $campaign->setMerchantPrice(MoneyUtil::formatPrice($product['merchantPrice']));
                    $campaign->setUnitPriceWithoutTaxForCampaign($productPrice);
                    $campaign->setUnitPriceForCampaign($productUnitPrice);

                    $productPriceTL = MoneyUtil::formatPrice($productPrice * $exchangeRate);
                    $productKDVTL = MoneyUtil::formatPrice(($taxRate * $productPriceTL));
                    $productUnitPriceTL = $productKDVTL + $productPriceTL;

                    $campaign->setUnitPriceWithoutTaxForCampaignTL($productPriceTL);
                    $campaign->setUnitPriceForCampaignTL($productUnitPriceTL);


                    $campaign->setTotalPriceWithoutTaxForCampaign($campaign->getQuantityForCampaign() * $campaign->getUnitPriceWithoutTaxForCampaign());
                    $campaign->setTotalPriceForCampaign($campaign->getQuantityForCampaign() * $campaign->getUnitPriceForCampaign());
                    $campaign->setTotalTaxForCampaign($campaign->getTotalPriceForCampaign() - $campaign->getTotalPriceWithoutTaxForCampaign());

                    $campaign->setTotalPriceWithoutTaxForCampaignTL($campaign->getQuantityForCampaign() * $campaign->getUnitPriceWithoutTaxForCampaignTL());
                    $campaign->setTotalPriceForCampaignTL($campaign->getQuantityForCampaign() * $campaign->getUnitPriceForCampaignTL());
                    $campaign->setTotalTaxForCampaignTL($campaign->getTotalPriceForCampaignTL() - $campaign->getTotalPriceWithoutTaxForCampaignTL());

                    /** Total */
                    $campaign->setGrandSubTotal($campaign->getTotalPriceWithoutTaxForCampaign() + $campaign->getTotalPriceWithoutTax());
                    $campaign->setGrandTotalTax($campaign->getTotalTaxForCampaign() + $campaign->getTotalTax());
                    $campaign->setGrandTotal($campaign->getTotalPriceForCampaign() + $campaign->getTotalPrice());
                    $campaign->setGrandTotalDiscountAmount(($basketProduct->getQuantity() * $campaign->getUnitPriceWithoutTax()) - $campaign->getGrandSubTotal());

                    $campaign->setGrandSubTotalTL($campaign->getTotalPriceWithoutTaxForCampaignTL() + $campaign->getTotalPriceWithoutTaxTL());
                    $campaign->setGrandTotalTaxTL($campaign->getTotalTaxForCampaignTL() + $campaign->getTotalTaxTL());
                    $campaign->setGrandTotalTL($campaign->getTotalPriceForCampaignTL() + $campaign->getTotalPriceTL());
                    $campaign->setGrandTotalDiscountAmountTL(MoneyUtil::formatPrice($campaign->getGrandTotalDiscountAmount() * $exchangeRate));

                    $basketProduct->setCampaign($campaign);
                }

                return $basketProduct;
            });

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][prepareBasketProductsWithoutQuantityControl] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][prepareBasketProductsWithoutQuantityControl] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|null
     */
    protected function prepareBasketSummary(Basket $basket)
    {
        try {
            $exchangeService = $this->container->get(ExchangeService::class);

            $subTotal = 0;
            $grandTotalKDV = 0;
            $grandTotal = 0;

            $subTotalTL = 0;
            $grandTotalKDVTL = 0;
            $grandTotalTL = 0;

            $couponDiscount = 0;
            $couponDiscountTL = 0;

            $grandTotalCampaignDiscount = 0;
            $grandTotalCampaignDiscountTL = 0;

            $rates = $exchangeService->getRates();

            $usdExchangeRate = $rates['usd']['rate'];
            $eurExchangeRate = $rates['eur']['rate'];
            $totalCargoPrice = 0;
            $totalCargoPriceTL = 0;
            $totalDesi = 0;

            $isAllBircomProducts = true;

            foreach ($basket->getBasketProducts() as $basketProduct) {
                /** @var BasketProduct $basketProduct */
                $product = (array)$basketProduct->getProduct();

                if ($product['currencyId'] == Currency::ID_EUR) {
                    $exchangeRate = $eurExchangeRate;
                } elseif ($product['currencyId'] == Currency::ID_USD) {
                    $exchangeRate = $usdExchangeRate;
                } else {
                    $this->logger->critical('Invalid currency. As default used USD exchange rate.', [
                        'product' => $product,
                    ]);

                    $exchangeRate = $usdExchangeRate;
                }

                if ($product['merchantId'] != Merchant::ID_BIRCOM) {
                    $isAllBircomProducts = false;
                }

                $productDesi = CargoUtil::formatDesi(
                    $basketProduct->getQuantity() * $this->cargoService->getDesi(
                        $product['width'],
                        $product['height'],
                        $product['depth']
                    )
                );

                $cargoPriceTL = $this->cargoService->calculateCargoPrice(CargoCompany::YURTICI_ID, $productDesi, $product['weight']);

                $totalDesi += $productDesi;
                $totalCargoPriceTL += MoneyUtil::formatPrice($cargoPriceTL);

                $basketProduct->setDesi($productDesi);

                $taxRate = $product['tax_rate'];

                if ($basketProduct->getCampaign() instanceof Campaign) {
                    $productPrice = null;
                    $productUnitPrice = null;
                    $productUnitPriceTL = null;

                    $subTotal += $basketProduct->getCampaign()->getGrandSubTotal();
                    $grandTotalKDV += $basketProduct->getCampaign()->getGrandTotalTax();
                    $grandTotalCampaignDiscount += $basketProduct->getCampaign()->getGrandTotalDiscountAmount();
                    $grandTotal += $basketProduct->getCampaign()->getGrandTotal();

                    $subTotalTL += $basketProduct->getCampaign()->getGrandSubTotalTL();
                    $grandTotalKDVTL += $basketProduct->getCampaign()->getGrandTotalTaxTL();
                    $grandTotalCampaignDiscountTL += $basketProduct->getCampaign()->getGrandTotalDiscountAmountTL();
                    $grandTotalTL += $basketProduct->getCampaign()->getGrandTotalTL();

                    $totalPriceWithoutTax = $basketProduct->getCampaign()->getGrandSubTotal();
                    $totalPrice = $basketProduct->getCampaign()->getGrandTotal();
                    $totalPriceTL = $basketProduct->getCampaign()->getGrandTotalTL();

                } else {
                    $productPrice = MoneyUtil::formatPrice($product['price']);

                    $productKDV = MoneyUtil::formatPrice(($taxRate * $productPrice));
                    $productUnitPrice = $productKDV + $productPrice;

                    $productPriceTL = MoneyUtil::formatPrice($productPrice * $exchangeRate);
                    $productKDVTL = MoneyUtil::formatPrice(($taxRate * $productPriceTL));
                    $productUnitPriceTL = $productKDVTL + $productPriceTL;

                    $basketProduct->setUnitPriceWithoutTax(MoneyUtil::formatPrice($productPrice));
                    $basketProduct->setUnitPrice(MoneyUtil::formatPrice($productUnitPrice));
                    $basketProduct->setUnitPriceTL(MoneyUtil::formatPrice($productUnitPriceTL));

                    $subTotal += ($productPrice * $basketProduct->getQuantity());
                    $grandTotalKDV += ($productKDV * $basketProduct->getQuantity());
                    $grandTotal += ($productUnitPrice * $basketProduct->getQuantity());

                    $subTotalTL += ($productPriceTL * $basketProduct->getQuantity());
                    $grandTotalKDVTL += ($productKDVTL * $basketProduct->getQuantity());
                    $grandTotalTL += ($productUnitPriceTL * $basketProduct->getQuantity());

                    $totalPriceWithoutTax = $productPrice * $basketProduct->getQuantity();
                    $totalPrice = $productUnitPrice * $basketProduct->getQuantity();
                    $totalPriceTL = $productUnitPriceTL * $basketProduct->getQuantity();
                }

                $basketProduct->setTotalPriceWithoutTax(MoneyUtil::formatPrice($totalPriceWithoutTax));
                $basketProduct->setTotalPrice(MoneyUtil::formatPrice($totalPrice));
                $basketProduct->setTotalPriceTL(MoneyUtil::formatPrice($totalPriceTL));

                $basketProduct->setExchangeRate($exchangeRate);
                $basketProduct->setCargoPriceTL(0);
                $basketProduct->setCargoPrice(0);
            }

            if ($totalCargoPriceTL > 0) {
                $totalCargoPriceTL = MoneyUtil::formatPrice(CargoUtil::getYurticiCargoFinalCargoPrice($totalCargoPriceTL));

                $desiFactor = $totalCargoPriceTL / $totalDesi;

                foreach ($basket->getBasketProducts() as $basketProduct) {
                    $basketProduct->setCargoPrice(MoneyUtil::formatPrice(($desiFactor * $basketProduct->getDesi()) / $basketProduct->getExchangeRate()));
                    $basketProduct->setCargoPriceTL(MoneyUtil::formatPrice($desiFactor * $basketProduct->getDesi()));
                }

                $grandTotalTL += $totalCargoPriceTL;

                $totalCargoPrice = $totalCargoPriceTL / $usdExchangeRate;

                $grandTotal += $totalCargoPrice;
            }

            $basketSummaryDTO = new BasketSummaryDTO();
            $basketSummaryDTO->setSubTotal(MoneyUtil::formatPrice($subTotal));
            $basketSummaryDTO->setGrandTotalKDV(MoneyUtil::formatPrice($grandTotalKDV));
            $basketSummaryDTO->setGrandTotal(MoneyUtil::formatPrice($grandTotal));

            $basketSummaryDTO->setSubTotalTL(MoneyUtil::formatPrice($subTotalTL));
            $basketSummaryDTO->setGrandTotalKDVTL(MoneyUtil::formatPrice($grandTotalKDVTL));
            $basketSummaryDTO->setGrandTotalTL(MoneyUtil::formatPrice($grandTotalTL));

            $basketSummaryDTO->setCouponDiscount(MoneyUtil::formatPrice($couponDiscount));
            $basketSummaryDTO->setCouponDiscountTL(MoneyUtil::formatPrice($couponDiscountTL));

            $basketSummaryDTO->setCampaignDiscount(MoneyUtil::formatPrice($grandTotalCampaignDiscount));
            $basketSummaryDTO->setCampaignDiscountTL(MoneyUtil::formatPrice($grandTotalCampaignDiscountTL));

            $basketSummaryDTO->setTotalDiscount($basketSummaryDTO->getCouponDiscount() + $basketSummaryDTO->getCampaignDiscount());
            $basketSummaryDTO->setTotalDiscountTL($basketSummaryDTO->getCouponDiscountTL() + $basketSummaryDTO->getCampaignDiscountTL());

            $basketSummaryDTO->setCargoPrice(MoneyUtil::formatPrice($totalCargoPrice));
            $basketSummaryDTO->setCargoPriceTL(MoneyUtil::formatPrice($totalCargoPriceTL));

            $basketSummaryDTO->setIsAllBircomProducts($isAllBircomProducts);

            $basket->setSummary($basketSummaryDTO);

            return $basket;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][prepareBasketSummary] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][prepareBasketSummary] %s', $e), [
                'basketId' => $basket->getId(),
            ]);
        }

        return null;
    }

    /**
     * @param User $user
     */
    public function clearBasket(User $user)
    {
        try {
            $basket = $this->getCurrentBasketByUser($user);

            if (!($basket instanceof Basket)) {
                throw new \LogicException();
            }

            $basket->setBasketProducts(new ArrayCollection());
            $basket->setShippingAddress(null);
            $basket->setBillingAddress(null);

            $this->entityManager->persist($basket);
            $this->entityManager->flush($basket);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][clearBasket] %s', $e), [
                'userId' => $user->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][clearBasket] %s', $e), [
                'userId' => $user->getId(),
            ]);
        }
    }

    /**
     * @param array $newBasketProducts
     * @param Collection $userBasketProducts
     *
     * @return bool|null|array
     */
    public function checkQuantityForBasket(array $newBasketProducts, Collection $userBasketProducts)
    {
        try {
            if ($userBasketProducts->isEmpty()) {
                $result['status'] = true;

                return $result;
            }

            $insufficientQuantityProducts = [];
            /** @var BasketProduct $newBasketProduct */
            foreach ($newBasketProducts as $newBasketProduct) {
                $newQuantityForBasketProduct = $newBasketProduct->getQuantity();

                /** @var BasketProduct $userBasketProduct */
                foreach ($userBasketProducts as $userBasketProduct) {
                    if ($newBasketProduct->getProductId() == $userBasketProduct->getProductId() &&
                        $newBasketProduct->getMerchant()->getId() == $userBasketProduct->getMerchant()->getId()) {
                        $newQuantityForBasketProduct += $userBasketProduct->getQuantity();
                        $currentBasketProduct = $userBasketProduct;
                        break;
                    }
                }

                $productFilter = new ProductFilter();
                $productFilter->setSectorId($newBasketProduct->getBasket()->getSector()->getId());
                $productFilter->setProductId($newBasketProduct->getProductId());
                $productFilter->setMerchantId($newBasketProduct->getMerchant()->getId());

                $productSearchRequest = new ProductSearchRequest();
                $productSearchRequest->setFilter($productFilter);
                $productSearchRequest->setIncludes(['platforms', 'segmentPrices']);

                $product = $this->productService->getProductById($productSearchRequest);

                if (empty($product) || !in_array('quantity', $product)) {
                    return null;
                }

                if ($newQuantityForBasketProduct > $product['quantity']) {
                    $resultArray ['newBasketProductRequest'] = $newBasketProduct;
                    $resultArray ['userBasketProduct'] = $currentBasketProduct ?? null;
                    $resultArray ['product'] = $product;
                    $resultArray ['newQuantityForBasketProduct'] = $newQuantityForBasketProduct;
                    array_push($insufficientQuantityProducts, $resultArray);
                }
            }

            if (count($insufficientQuantityProducts) > 0) {
                $result['status'] = false;
                $result['insufficientQuantityProducts'] = $insufficientQuantityProducts;
            } else {
                $result['status'] = true;
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][checkQuantityForBasket] %s', $e), [
                'newBasketProducts' => $newBasketProducts,
                'userBasketProducts' => $userBasketProducts->toArray(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][checkQuantityForBasket] %s', $e), [
                'newBasketProducts' => $newBasketProducts,
                'userBasketProducts' => $userBasketProducts->toArray(),
            ]);
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return BasketFilter
     */
    public function prepareBasketFilterWithRequest(Request $request): BasketFilter
    {
        $basketFilter = new BasketFilter();

        try {
            if ($startedAt = $request->query->get('startedAt')) {
                $basketFilter->setStartedAt(new \DateTime(str_replace('/', '-', $startedAt)));
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[BasketService][prepareBasketFilterWithRequest] %s', $e), [
                'startedAt' => $request->query->get('startedAt'),
            ]);
        }

        try {
            if ($endAt = $request->query->get('endAt')) {
                $basketFilter->setEndAt(new \DateTime(str_replace('/', '-', $endAt)));
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('[BasketService][prepareBasketFilterWithRequest] %s', $e), [
                'endAt' => $request->query->get('endAt'),
            ]);
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', BasketFilter::DEFAULT_LIMIT);

        if ($limit > BasketFilter::DEFAULT_LIMIT) {
            $limit = BasketFilter::DEFAULT_LIMIT;
        }

        $basketFilter->setPage($page);
        $basketFilter->setLimit($limit);

        if (!empty($request->query->get('basketId'))) {
            $basketFilter->setBasketId($request->query->getInt('basketId'));
        }

        if (!empty($request->query->get('userFullName'))) {
            $basketFilter->setUserFullName($request->query->get('userFullName'));
        }

        if (!empty($request->query->get('productName'))) {
            $basketFilter->setUserFullName($request->query->get('productName'));
        }

        if (!empty($request->query->get('productId'))) {
            $basketFilter->setProductId($request->query->getInt('productId'));
        }

        if (!empty($request->query->get('sellerMerchantIds')) && is_array($request->query->get('sellerMerchantIds'))) {
            $basketFilter->setSellerMerchantIds($request->query->get('sellerMerchantIds'));
        }

        if (!empty($request->query->get('buyerMerchantIds')) && is_array($request->query->get('buyerMerchantIds'))) {
            $basketFilter->setBuyerMerchantIds($request->query->get('buyerMerchantIds'));
        }

        if (!empty($request->query->get('userIds')) && is_array($request->query->get('userIds'))) {
            $basketFilter->setUserIds($request->query->get('userIds'));
        }

        if (!empty($request->query->get('sectorIds')) && is_array($request->query->get('sectorIds'))) {
            $basketFilter->setSectorIds($request->query->get('sectorIds'));
        }

        if (!empty($request->query->get('minQuantity'))) {
            $basketFilter->setMinQuantity($request->query->getInt('minQuantity'));
        }

        if (!empty($request->query->get('maxQuantity'))) {
            $basketFilter->setMaxQuantity($request->query->getInt('maxQuantity'));
        }

        return $basketFilter;
    }

    /**
     * @param BasketFilter $basketFilter
     *
     * @return mixed|Pagerfanta|null
     */
    public function getBasketsWithFilter(BasketFilter $basketFilter)
    {
        try {
            $parameters = null;
            $queryBuilder = $this->basketRepository->createQueryBuilder('b')
                ->select('b', 'u', 'bp')
                ->innerJoin('b.user', 'u')
                ->innerJoin('b.basketProducts', 'bp');

            if (!empty($basketFilter->getBasketId())) {
                $queryBuilder->andWhere('b.id = :basketId');

                $parameters['basketId'] = $basketFilter->getBasketId();
            }

            if (!empty($basketFilter->getUserFullName())) {
                $queryBuilder->andWhere("CONCAT_WS(' ', u.firstName, u.lastName) LIKE :userFullName");

                $parameters['userFullName'] = "%{$basketFilter->getUserFullName()}%";
            }

            if (!empty($basketFilter->getProductId())) {
                $queryBuilder->andWhere('bp.productId = :productId');

                $parameters['productId'] = $basketFilter->getProductId();
            }

            if (!empty($basketFilter->getSellerMerchantIds())) {
                $queryBuilder->innerJoin('bp.merchant', 'sellerMerchant')
                ->andWhere('sellerMerchant.id IN (:sellerMerchantIds)');

                $parameters['sellerMerchantIds'] = $basketFilter->getSellerMerchantIds();
            }

            if (!empty($basketFilter->getBuyerMerchantIds())) {
                $queryBuilder->innerJoin('u.merchant', 'buyerMerchant')
                    ->andWhere('buyerMerchant.id IN (:buyerMerchantIds)');

                $parameters['buyerMerchantIds'] = $basketFilter->getBuyerMerchantIds();
            }

            if (!empty($basketFilter->getUserIds())) {
                $queryBuilder->andWhere('u.id IN (:userIds)');

                $parameters['userIds'] = $basketFilter->getUserIds();
            }

            if (!empty($basketFilter->getSectorIds())) {
                $queryBuilder->innerJoin('u.merchant', 'm')
                    ->innerJoin('m.merchantSectors', 'ms', Join::WITH, $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->in('ms.sector', ':sectorIds')
                    ));

                $parameters['sectorIds'] = $basketFilter->getSectorIds();
            }

            if (!empty($basketFilter->getMinQuantity())) {
                $queryBuilder->andWhere('bp.quantity >= :minQuantity');

                $parameters['minQuantity'] = $basketFilter->getMinQuantity();
            }

            if (!empty($basketFilter->getMaxQuantity())) {
                $queryBuilder->andWhere('bp.quantity <= :maxQuantity');

                $parameters['maxQuantity'] = $basketFilter->getMaxQuantity();
            }

            if (!empty($basketFilter->getStartedAt())) {
                $queryBuilder->andWhere('b.createdAt >= :startedAt');

                $parameters['startedAt'] = $basketFilter->getStartedAt();
            }

            if (!empty($basketFilter->getEndAt())) {
                $queryBuilder->andWhere('b.createdAt <= :endAt');

                $parameters['endAt'] = $basketFilter->getEndAt();
            }

            if (!empty($parameters)) {
                $queryBuilder->setParameters($parameters);
            }

            if (!empty($basketFilter->getOrderBy())) {
                $queryBuilder->orderBy("b.{$basketFilter->getOrderBy()}", $basketFilter->getSortBy());
            }

            if ($basketFilter->getPaginate()) {
                $baskets = new Pagerfanta(new DoctrineORMAdapter($queryBuilder));
                $baskets->setAllowOutOfRangePages(true);
                $baskets
                    ->setMaxPerPage($basketFilter->getLimit())
                    ->setCurrentPage($basketFilter->getPage());
            } else {
                $baskets = $queryBuilder->getQuery()->getResult();
            }

            if ($basketFilter->getAddSummary()) {
                foreach ($baskets->getCurrentPageResults() as $basket) {
                    $this->entityManager->detach($basket);
                    $basket = $this->prepareBasketProductsWithoutQuantityControl($basket);
                    $basket = $this->prepareBasketSummary($basket);
                }
            }

            return $baskets;
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][getBasketsWithFilter] %s', $e), [
                'basketFilter' => [
                    'basketId' => $basketFilter->getBasketId(),
                    'productId' => $basketFilter->getProductId(),
                    'buyerMerchantIds' => $basketFilter->getBuyerMerchantIds(),
                    'sellerMerchantIds' => $basketFilter->getSellerMerchantIds(),
                    'sectorIds' => $basketFilter->getSectorIds(),
                ],
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][getBasketsWithFilter] %s', $e), [
                'basketFilter' => [
                    'basketId' => $basketFilter->getBasketId(),
                    'productId' => $basketFilter->getProductId(),
                    'buyerMerchantIds' => $basketFilter->getBuyerMerchantIds(),
                    'sellerMerchantIds' => $basketFilter->getSellerMerchantIds(),
                    'sectorIds' => $basketFilter->getSectorIds(),
                ],
            ]);
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return Basket|null
     */
    public function getCurrentBasketByUser(User $user): ?Basket
    {
        try {
            return $this->basketRepository->createQueryBuilder('b')
                ->select('b')
                ->where('b.user = :userId')
                ->andWhere('b.sector = :sectorId')
                ->setParameters([
                    'userId' => $user->getId(),
                    'sectorId' => $user->getDefaultSector(),
                ])
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('[BasketService][getCurrentBasketByUser] %s', $e), [
                'userId' => $user->getId(),
            ]);
        } catch (\Error $e) {
            $this->logger->error(sprintf('[BasketService][getCurrentBasketByUser] %s', $e), [
                'userId' => $user->getId(),
            ]);
        }

        return null;
    }
}
