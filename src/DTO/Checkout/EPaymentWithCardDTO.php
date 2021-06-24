<?php

namespace App\DTO\Checkout;

class EPaymentWithCardDTO
{
    /**
     * @var string|null
     */
    private $cardNumber;

    /**
     * @var string|null
     */
    private $cardHolderName;

    /**
     * @var string|null
     */
    private $cardExpireYear;

    /**
     * @var string|null
     */
    private $cardExpireMonth;

    /**
     * @var string|null
     */
    private $cardCvc;

    /**
     * @var string|null
     */
    private $installment;

    /**
     * @var string|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $currencyId;

    /**
     * @var string|null
     */
    private $registerCard;

    /**
     * @var string|null
     */
    private $acceptAgreement;

    /**
     * @return string|null
     */
    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    /**
     * @param string|null $cardNumber
     */
    public function setCardNumber(?string $cardNumber): void
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @return string|null
     */
    public function getCardHolderName(): ?string
    {
        return $this->cardHolderName;
    }

    /**
     * @param string|null $cardHolderName
     */
    public function setCardHolderName(?string $cardHolderName): void
    {
        $this->cardHolderName = $cardHolderName;
    }

    /**
     * @return string|null
     */
    public function getCardExpireYear(): ?string
    {
        return $this->cardExpireYear;
    }

    /**
     * @param string|null $cardExpireYear
     */
    public function setCardExpireYear(?string $cardExpireYear): void
    {
        $this->cardExpireYear = $cardExpireYear;
    }

    /**
     * @return string|null
     */
    public function getCardExpireMonth(): ?string
    {
        return $this->cardExpireMonth;
    }

    /**
     * @param string|null $cardExpireMonth
     */
    public function setCardExpireMonth(?string $cardExpireMonth): void
    {
        $this->cardExpireMonth = $cardExpireMonth;
    }

    /**
     * @return string|null
     */
    public function getCardCvc(): ?string
    {
        return $this->cardCvc;
    }

    /**
     * @param string|null $cardCvc
     */
    public function setCardCvc(?string $cardCvc): void
    {
        $this->cardCvc = $cardCvc;
    }

    /**
     * @return string|null
     */
    public function getInstallment(): ?string
    {
        return $this->installment;
    }

    /**
     * @param string|null $installment
     */
    public function setInstallment(?string $installment): void
    {
        $this->installment = $installment;
    }

    /**
     * @return string|null
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @param string|null $amount
     */
    public function setAmount(?string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string|null
     */
    public function getCurrencyId(): ?string
    {
        return $this->currencyId;
    }

    /**
     * @param string|null $currencyId
     */
    public function setCurrencyId(?string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    /**
     * @return string|null
     */
    public function getRegisterCard(): ?string
    {
        return $this->registerCard;
    }

    /**
     * @param string|null $registerCard
     */
    public function setRegisterCard(?string $registerCard): void
    {
        $this->registerCard = $registerCard;
    }

    /**
     * @return string|null
     */
    public function getAcceptAgreement(): ?string
    {
        return $this->acceptAgreement;
    }

    /**
     * @param string|null $acceptAgreement
     */
    public function setAcceptAgreement(?string $acceptAgreement): void
    {
        $this->acceptAgreement = $acceptAgreement;
    }
}
