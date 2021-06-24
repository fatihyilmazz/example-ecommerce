<?php

namespace App\Exception;

interface ApiExceptionInterface
{
    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors): void;

    /**
     * @param $key
     * @param $error
     * @return void
     */
    public function addError($key, $error): void;
}