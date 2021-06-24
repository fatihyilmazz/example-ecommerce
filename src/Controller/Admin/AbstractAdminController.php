<?php

namespace App\Controller\Admin;

use App\Entity\User;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdminController extends AbstractController
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \JMS\Serializer\SerializationContext|null
     */
    protected $serializationContext;

    /**
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;

        $this->setSerializationContext(new SerializationContext());
    }

    /**
     * @inheritdoc
     */
    protected function json($data, int $status = 200, array $headers = array(), array $context = array()): JsonResponse
    {
        $content = [
            'success' => true,
            'data' => $data
        ];

        $this->getSerializationContext()->setSerializeNull(true);

        $jsonData = $this->serializer->serialize($content, 'json', $this->getSerializationContext());

        return (new JsonResponse())->setJson($jsonData)->setStatusCode($status);
    }

    /**
     * @return \JMS\Serializer\SerializationContext
     */
    public function getSerializationContext(): ?\JMS\Serializer\SerializationContext
    {
        return $this->serializationContext;
    }

    /**
     * @param \JMS\Serializer\SerializationContext $serializationContext
     */
    public function setSerializationContext(\JMS\Serializer\SerializationContext $serializationContext): void
    {
        $this->serializationContext = $serializationContext;
    }

    /**
     * @return mixed|void|UserInterface|User
     */
    protected function getUser()
    {
        return parent::getUser();
    }


}
