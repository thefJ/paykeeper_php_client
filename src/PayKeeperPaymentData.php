<?php
declare(strict_types=1);

namespace ThefJ\PayKeeperClient;

readonly class PayKeeperPaymentData
{
    public function __construct(
        private float $payAmount,
        private string $clientName,
        private string $orderId,
        private string $clientEmail,
        private string $serviceName,
        private string $clientPhone,
    ) {}

    public function getPayAmount(): float
    {
        return $this->payAmount;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getClientPhone(): string
    {
        return $this->clientPhone;
    }
}