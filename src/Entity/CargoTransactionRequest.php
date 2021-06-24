<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("cargo_transaction_requests")
 * @ORM\Entity(repositoryClass="App\Repository\CargoTransactionRequestRepository")
 */
class CargoTransactionRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CargoCompany")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cargoCompany;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CargoTransactionRequestType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cargoTransactionRequestType;

    /**
     * @ORM\Column(type="text", nullable=true, length=25)
     */
    private $orderProductDocumentKey;

    /**
     * @ORM\Column(type="text", nullable=true, length=65535)
     */
    private $requestData;

    /**
     * @ORM\Column(type="text", nullable=true, length=65535)
     */
    private $responseData;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CargoTransactionRequestStatus", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cargoTransactionStatus;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CargoCompany
     */
    public function getCargoCompany(): CargoCompany
    {
        return $this->cargoCompany;
    }

    /**
     * @param CargoCompany $cargoCompany
     *
     * @return $this
     */
    public function setCargoCompany(CargoCompany $cargoCompany): self
    {
        $this->cargoCompany = $cargoCompany;

        return $this;
    }

    /**
     * @return CargoTransactionRequestType
     */
    public function getCargoTransactionRequestType(): CargoTransactionRequestType
    {
        return $this->cargoTransactionRequestType;
    }

    /**
     * @param CargoTransactionRequestType $cargoTransactionRequestType
     *
     * @return $this
     */
    public function setCargoTransactionRequestType(CargoTransactionRequestType $cargoTransactionRequestType): self
    {
        $this->cargoTransactionRequestType = $cargoTransactionRequestType;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderProductDocumentKey(): string
    {
        return $this->orderProductDocumentKey;
    }

    /**
     * @param string $orderProductDocumentKey
     *
     * @return $this
     */
    public function setOrderProductDocumentKey(string $orderProductDocumentKey): self
    {
        $this->orderProductDocumentKey = $orderProductDocumentKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestData(): ?string
    {
        return $this->requestData;
    }

    /**
     * @param string $requestData
     *
     * @return $this
     */
    public function setRequestData(string $requestData): self
    {
        $this->requestData = $requestData;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseData(): ?string
    {
        return $this->responseData;
    }

    /**
     * @param string $responseData
     *
     * @return $this
     */
    public function setResponseData(string $responseData): self
    {
        $this->responseData = $responseData;

        return $this;
    }

    /**
     * @return CargoTransactionRequestStatus
     */
    public function getCargoTransactionStatus(): CargoTransactionRequestStatus
    {
        return $this->cargoTransactionStatus;
    }

    /**
     * @param CargoTransactionRequestStatus $cargoTransactionStatus
     *
     * @return $this
     */
    public function setCargoTransactionStatus(CargoTransactionRequestStatus $cargoTransactionStatus): self
    {
        $this->cargoTransactionStatus = $cargoTransactionStatus;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
