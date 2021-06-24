<?php

namespace App\Command;

use App\Entity\Order;
use Psr\Log\LoggerInterface;
use App\Service\CargoService;
use App\Service\OrderService;
use App\Entity\OrderProductStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckPendingToApproveOrderProduct extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var CargoService
     */
    protected $cargoService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected static $defaultName = 'order:check-delivered-pending-to-approve-order-products';

    /**
     * @param EntityManagerInterface $entityManager
     * @param OrderService $orderService
     * @param CargoService $cargoService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderService $orderService,
        CargoService $cargoService,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->cargoService = $cargoService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks delivered order products before 3 days and updates order product as completed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $deliveredOrderProducts = $this->orderService->getDeliveredOrderProducts();

        if ($deliveredOrderProducts->isEmpty()) {
            $io->success('[CheckWaitingToApproveOrderProduct][execute] No delivered order products pending to approve.');

            return;
        }

        foreach ($deliveredOrderProducts as $deliveredOrderProduct) {
            $deliveredOrderProduct->setOrderProductStatus(
                $this->entityManager->getReference(
                    OrderProductStatus::class,
                    OrderProductStatus::ID_COMPLETED
                )
            );

            $updateOrderProduct = $this->orderService->updateOrderProductStatusAsDelivered($deliveredOrderProduct);

            if (!($updateOrderProduct instanceof Order)) {
                $this->logger->error(sprintf(
                    '[CheckPendingToApproveOrderProduct][execute] Order product status could not changed successfully. orderProductId: %s',
                    $deliveredOrderProduct->getId()
                ));
            }
        }

        $io->success('[CheckWaitingToApproveOrderProduct][execute] Order products status changed as delivered successfully');
    }
}
