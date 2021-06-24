<?php

namespace App\Command;

use App\Entity\OrderProduct;
use Psr\Log\LoggerInterface;
use App\Service\CargoService;
use App\Service\OrderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCargoTrackingIdCommand extends Command
{
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

    protected static $defaultName = 'order:check-order-products-to-wait-preparing-document';

    /**
     * @param OrderService $orderService
     * @param CargoService $cargoService
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderService $orderService,
        CargoService $cargoService,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->orderService = $orderService;
        $this->cargoService = $cargoService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks cargo tracking id of shipped products and updates order products cargo tracking ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $shippedOrderProducts = $this->orderService->getOrderProductsToWaitPreparingDocument();

        if ($shippedOrderProducts->isEmpty()) {
            $io->success('[CheckCargoTrackingIdCommand][execute] No order products to check cargo tracking ids.');

            return;
        }

        $cargoTrackingIdChangedOrderProducts = [];
        foreach ($shippedOrderProducts as $shippedOrderProduct) {
            /** @var OrderProduct $shippedOrderProduct */
            $orderProductCargoTrackingId = $this->cargoService->getCargoTrackingId($shippedOrderProduct->getDocumentKey());

            if (!empty($orderProductCargoTrackingId)) {
                $shippedOrderProduct->setCargoTrackingId($orderProductCargoTrackingId);

                $cargoTrackingIdChangedOrderProducts[] = $shippedOrderProduct;
            }
        }

        if (empty($cargoTrackingIdChangedOrderProducts)) {
            $io->success('No order products with changed cargo tracking id.');

            return;
        }

        $orderProducts = $this->orderService->updateOrderProducts($cargoTrackingIdChangedOrderProducts);

        if (!empty($orderProducts)) {
            $io->success(sprintf('%d Order products cargo tracking id changed successfully.', count($orderProducts)));

            $this->logger->info(sprintf('[CheckCargoTrackingIdCommand][execute] %d Order products cargo tracking id updated successfully.', count($orderProducts)));

            return;
        }

        $io->warning('Order products cargo tracking id could not been updated.');
    }
}
