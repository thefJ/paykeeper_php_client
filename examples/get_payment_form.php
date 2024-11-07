<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use ThefJ\PayKeeperClient\PayKeeperClient;
use ThefJ\PayKeeperClient\PayKeeperPaymentData;

$payKeeperClient = new PayKeeperClient(
    new Client(),
    getenv('PAY_KEEPER_SERVER'),
    getenv('PAY_KEEPER_LOGIN'),
    getenv('PAY_KEEPER_PASSWORD')
);

$paymentData = new PayKeeperPaymentData(
    300.30, 
    'Иванов Иван', 
    'Заказ № 10',
    'test@test.com',
    'Товар',
    '+7 (777) 777-77-77'
);

$formLink = $payKeeperClient->getFormLink($paymentData);