<?php

namespace App\Utils;

class Generate
{
    /**
     * @param int $length
     * @return string
     */
    public static function key($length = 10)
    {
        $charPool = array_merge(
            range('a', 'z'),
            range('A', 'Z'),
            range(0, 9)
        );

        $key = "";
        for ($i=0; $i < $length; $i++) {
            $key .= $charPool[mt_rand(0, count($charPool) - 1)];
        }

        return $key;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function string($length = 10)
    {
        $charPool = array_merge(
            range('a', 'z'),
            range('A', 'Z'),
            range(0, 9)
        );

        $string = "";
        for ($i=0; $i < $length; $i++) {
            $string .= $charPool[mt_rand(0, count($charPool) - 1)];
        }

        return $string;
    }

    /**
     * @param int $length
     * @return int
     */
    public static function number($length = 10)
    {
        $charPool = array_merge(range(0, 9));

        $integer = "";
        for ($i=0; $i < $length; $i++) {
            $integer .= $charPool[mt_rand(0, count($charPool) - 1)];
        }

        return (int)$integer;
    }

}
