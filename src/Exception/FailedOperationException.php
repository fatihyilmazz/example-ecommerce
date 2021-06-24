<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class FailedOperationException extends AbstractApiException
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_SERVICE_UNAVAILABLE);
    }

}