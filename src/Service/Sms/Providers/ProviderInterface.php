<?php

namespace App\Service\Sms\Providers;

interface ProviderInterface
{
    /**
     * Sends single SMS
     *
     * @param string $phone
     * @param string $message
     *
     * @return mixed
     */
    public function sendSms($phone, $message);

    /**
     * Sends multiple SMS
     *
     * @param array $phones
     * @param array $messages
     * @param \DateTime $sendDate
     * @param \DateTime $endDate
     *
     * @return mixed
     */
    public function sendBulkSms(array $phones, array $messages, \DateTime $sendDate, \DateTime $endDate);
}
