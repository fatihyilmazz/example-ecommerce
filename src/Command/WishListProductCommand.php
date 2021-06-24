<?php

namespace App\Command;

use App\Service\MailService;
use Psr\Log\LoggerInterface;
use App\Service\ProductService;
use App\Entity\WishListProduct;
use App\Service\WishListProductService;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ProductManagement\Request\Product\ProductFilter;
use App\Service\ProductManagement\Request\Product\ProductSearchRequest;

class WishListProductCommand extends Command
{
    protected static $defaultName = 'wishList:send';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var WishListProductService
     */
    protected $wishListProductService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * @param LoggerInterface $logger
     * @param WishListProductService $wishListProductService
     * @param MailService $mailService
     * @param TwigEnvironment $twig
     * @param ProductService $productService
     */
    public function __construct(
        LoggerInterface $logger,
        WishListProductService $wishListProductService,
        MailService $mailService,
        TwigEnvironment $twig,
        ProductService $productService
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->wishListProductService = $wishListProductService;
        $this->mailService = $mailService;
        $this->twig = $twig;
        $this->productService = $productService;
    }

    protected function configure()
    {
        $this->setDescription("Send email for user's wish list");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $pendingWishListProducts = $this->wishListProductService->getPendingWishListProducts();

            if (empty($pendingWishListProducts)) {
                $this->logger->info(sprintf('[WishListCommand][execute] No pending wishListProduct found to notify.'));

                $io->success(sprintf('[WishListCommand][execute] WishListCommand has run'));

                return;
            }

            foreach ($pendingWishListProducts as $pendingWishListProduct) {
                $productFilter = new ProductFilter();
                $productFilter->setSectorId($pendingWishListProduct->getSector()->getId());
                $productFilter->setProductId($pendingWishListProduct->getProductId());
                $productFilter->setMerchantId($pendingWishListProduct->getMerchant()->getId());

                $productSearchRequest = new ProductSearchRequest();
                $productSearchRequest->setFilter($productFilter);
                $productSearchRequest->setIncludes(['categories', 'platforms', 'segmentPrices']);

                $product = $this->productService->getProductById($productSearchRequest);

                if (empty($product)) {
                    $now = new \DateTime();
                    $timeDifference = $now->diff($pendingWishListProduct->getCreatedAt());

                    if ($timeDifference->y >= WishListProduct::NOTIFICATION_EXPIRE_YEAR) {
                        $result = $this->wishListProductService->delete($pendingWishListProduct);

                        if (!$result) {
                            $this->logger->error(
                                sprintf(
                                    '[WishListCommand][execute] WishListProduct could not been deleted. WishListProductId: %s',
                                    $pendingWishListProduct->getId()
                                )
                            );
                        }
                    }

                    $pendingWishListProduct->setSaleClosedAt(new \DateTime());

                    $this->wishListProductService->update($pendingWishListProduct);

                    $this->logger->info(
                        sprintf(
                            '[WishListCommand][execute] WishListProduct is not available for sale. WishListProductId: %s',
                            $pendingWishListProduct->getId()
                        )
                    );

                    continue;
                }

                if ($product['quantity'] <= 0) {
                    $io->note(
                        sprintf('[WishListCommand][execute] There is not stock for product. ProductId: %s, Stock:%s',
                            $product['id'],
                            $product['quantity']
                        )
                    );

                    continue;
                }

                $templateContent = $this->twig->render('commons/mails/stock_information.html.twig', [
                    'user' => $pendingWishListProduct->getUser(),
                    'product' => $product,
                ]);

                $mailResult = $this->mailService->send($pendingWishListProduct->getUser()->getEmail(), 'Stok Bildirimi', $templateContent);

                if (empty($mailResult)) {
                    $this->logger->error(
                        sprintf(
                            '[WishListCommand][execute] Stock reminder e-mail could not be sent to the user. UserId: %s, WishListProductId: %s, MailTransactionId: %s',
                            $pendingWishListProduct->getUser()->getId(),
                            $pendingWishListProduct->getId(),
                            MailService::TYPE_WISH_LIST
                        )
                    );

                    continue;
                }

                $pendingWishListProduct->setNotifiedAt(new \DateTime());

                $wishListProduct = $this->wishListProductService->update($pendingWishListProduct);

                if (!($wishListProduct instanceof WishListProduct)) {
                    $this->logger->error(
                        sprintf(
                            '[WishListCommand][execute] Stock reminder e-mail forwarded to users But could not change wishListProduct status. WishListProductId: %s, MailTransactionId: %s',
                            $pendingWishListProduct->getId(),
                            MailService::TYPE_WISH_LIST
                        )
                    );

                    continue;
                }

                $this->logger->info(
                    sprintf(
                        '[WishListCommand][execute] Stock reminder e-mail forwarded to users. WishListProductId: %s, MailTransactionId: %s',
                        $pendingWishListProduct->getId(),
                        MailService::TYPE_WISH_LIST
                    )
                );
            }

            $this->logger->info(sprintf('[WishListCommand][execute] WishListCommand has run'));

            $io->success(sprintf('[WishListCommand][execute] WishListCommand has run'));
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('[WishListCommand][execute] %s', $e));
        }
    }
}
