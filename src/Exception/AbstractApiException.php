<?php

namespace App\Exception;

class AbstractApiException extends \Exception implements ApiExceptionInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @inheritdoc
     */
    public function addError($key, $error): void
    {
        $this->errors[$key] = $error;
    }
}