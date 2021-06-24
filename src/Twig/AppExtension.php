<?php

namespace App\Twig;

use Twig\TwigFilter;
use App\Entity\User;
use App\Entity\Sector;
use App\Utils\MoneyUtil;
use App\Entity\Merchant;
use App\Entity\Currency;
use App\Entity\CargoCompany;
use App\Service\CargoService;
use App\Service\OrderService;
use App\Service\SectorService;
use App\Entity\MerchantHistory;
use App\Service\MarketPlaceService;
use App\Entity\MerchantSectorHistory;
use Twig\Extension\AbstractExtension;
use App\Service\MerchantContactService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\DefectiveProductService;
use App\Entity\MerchantSectorHistoryStatus;
use App\Service\ProductManagement\ReportService;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends AbstractExtension
{
    // i also have knowledge about twig extension
    public function __construct() {

    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('xxx', [$this, 'getNetsisCurrencyCode']),
            new TwigFilter('xxx', [$this, 'formatTL']),
            new TwigFilter('xxxx', [$this, 'formatUSD']),
            new TwigFilter('xxxxx', [$this, 'formatEUR']),
            new TwigFilter('xxxxx', [$this, 'formatPrice']),
            new TwigFilter('xxxxx', [$this, 'displayTaxRate']),
            new TwigFilter('xxxxx', [$this, 'resizeImage']),
        ];
    }
}
