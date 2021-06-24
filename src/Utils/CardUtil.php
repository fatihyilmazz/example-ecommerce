<?php

namespace App\Utils;

class CardUtil
{
    /**
     * get bin number from card number
     *
     * @param string $cardNumber
     *
     * @return int
     */
    public static function getBinNumberFromCardNumber(string $cardNumber)
    {
        if (strlen($cardNumber) < 6) {
            throw new \InvalidArgumentException('Card number must be minimum 6 character');
        }

        return (int)substr($cardNumber, 0, 6);
    }

    /**
     * get bin number from card number
     *
     * @param string $cardNumber
     *
     * @return bool|string|null
     */
    public static function getMaskedCardNumber(string $cardNumber)
    {
        return substr($cardNumber, 0, 6) . '******' . substr($cardNumber, -4);
    }
}