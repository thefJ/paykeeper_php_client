<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use ThefJ\PayKeeperClient\PayKeeperClient;
use ThefJ\PayKeeperClient\PayKeeperInvoiceStatus;

$payKeeperClient = new PayKeeperClient(
    new Client(),
    getenv('PAY_KEEPER_SERVER'),
    getenv('PAY_KEEPER_LOGIN'),
    getenv('PAY_KEEPER_PASSWORD')
);

$invoiceStatus = $payKeeperClient->getInvoiceStatus('invoice_id');

if ($invoiceStatus == PayKeeperInvoiceStatus::Paid) {
    // Invoice is paid
}