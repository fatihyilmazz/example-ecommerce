<?php

namespace App\Utils;

class CargoUtil
{
    /**
     * Format desi
     *
     * @param float $desi
     *
     * @return float
     */
    public static function formatDesi(float $desi): float
    {
        return (float)number_format($desi, 2, '.', '');
    }

    /**
     * Format weight
     *
     * @param float $weight
     *
     * @return float
     */
    public static function formatWeight(float $weight): float
    {
        return (float)number_format($weight, 2, '.', '');
    }

    /**
     * @param $cargoPriceTL
     *
     * @return float|int
     */
    public static function getYurticiCargoFinalCargoPrice($cargoPriceTL)
    {
        $tax = 0.18;
        $serviceFee = 1.0235;

        return ($cargoPriceTL + ($cargoPriceTL * $tax)) * $serviceFee;
    }

    /**
     * @param int $merchantId
     * @param int $orderId
     *
     * @return string
     */
    public static function generateDocumentKey(int $merchantId, int $orderId): string
    {
        $documentKeyPrefix = 'K';
        $merchantIdPrefix = 'M';
        $orderIdPrefix = 'N';
        $maxLength = 20;
        $currentLength = strlen(sprintf('%s%s%s%s%s', $documentKeyPrefix, $merchantIdPrefix, $merchantId, $orderIdPrefix, $orderId));
        $freeLength = $maxLength - $currentLength;
        $randomNumber = null;

        if ($freeLength > 0) {
            $min = (int)str_repeat(9, $freeLength - 1) + 1;
            $max = (int)str_repeat(9, $freeLength);

            $randomNumber = mt_rand($min, $max);
        }

        return sprintf('%s%s%s%s%s%s', $documentKeyPrefix, $randomNumber, $merchantIdPrefix, $merchantId, $orderIdPrefix, $orderId);
    }
}
