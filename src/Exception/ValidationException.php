<?php

namespace App\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class ValidationException extends AbstractApiException
{
    /**
     * Prepare error messages for invalid form requests
     *
     * @param string $message
     * @param FormInterface $form
     */
    public function __construct(string $message, FormInterface $form)
    {
        foreach ($form->all() as $key => $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors(true) as $error) {
#                    $errors[$key] = $error->getMessage();
                    $this->addError($key, $error->getMessage());
                }
            }
        }

        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }

}