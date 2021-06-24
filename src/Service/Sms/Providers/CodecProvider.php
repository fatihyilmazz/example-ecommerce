<?php

namespace App\Service\Sms\Providers;

use App\Service\Sms\Providers\Codec\Parameter;
use App\Service\Sms\Providers\Codec\Service\BulkApiService;
use App\Service\Sms\Providers\Codec\Service\FastApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CodecProvider implements ProviderInterface
{
    /**
     * @var FastApiService
     */
    private $fastApiService;

    /**
     * @var BulkApiService
     */
    private $bulkApiService;

    public function __construct(ContainerInterface $container)
    {
        $this->fastApiService = new FastApiService(new Parameter($container));
        $this->bulkApiService = new BulkApiService(new Parameter($container));
    }

    /**
     * @inheritdoc
     */
    public function sendSms($phone, $message)
    {
        return $this->fastApiService->sendSms($phone, $message);
    }

    /**
     * @inheritdoc
     */
    public function sendBulkSms(array $phones, array $messages, \DateTime $sendDate, \DateTime $endDate)
    {
        //
    }

    /**
     * Generates a session id
     *
     * @return mixed
     */
    public function register()
    {
        //
    }

    /**
     * Starts sending bulk SMS via registered EU ID
     *
     * @param string $registerEuId
     *
     * @return mixed
     */
    public function sendStart($registerEuId)
    {
        //
    }
}
