<?php

namespace App\Utils;

use App\Entity\Currency;

class MoneyUtil
{
    /**
     * get formatted price
     *
     * @param float $price
     *
     * @return float
     */
    public static function formatPrice(float $price): float
    {
        return (float)number_format($price, 2, '.', '');
    }

    /**
     * get currency code by id
     *
     * @param int $currencyId
     *
     * @return string
     */
    public static function getCurrencyCodeById(int $currencyId): string
    {
        $currencies = [
            Currency::ID_TRY => Currency::CODE_TL,
            Currency::ID_USD => Currency::CODE_USD,
            Currency::ID_EUR => Currency::CODE_EUR,
        ];

        return $currencies[$currencyId];
    }

    /**
     * Get currency code by id for payment
     *
     * @param int $currencyId
     *
     * @return string
     */
    public static function getCurrencyCodeForPaymentService(int $currencyId): string
    {
        $currencies = [
            Currency::ID_TRY => Currency::CODE_TRY,
            Currency::ID_USD => Currency::CODE_USD,
            Currency::ID_EUR => Currency::CODE_EUR,
        ];

        return $currencies[$currencyId];
    }

    /**
     * Convert price for netsis
     *
     * @param float $price
     *
     * @return string
     */
    public static function netsisFormat(float $price)
    {
        return number_format($price, 2, ',', '.');
    }

    public static function displayFormatTL($price)
    {
        return number_format($price, 2, '.', ',');
    }

    public static function displayFormatUSD($price)
    {
        return number_format($price, 2, '.', ',');
    }

    public static function displayFormatEUR($price)
    {
        return number_format($price, 2, '.', ',');
    }
}
