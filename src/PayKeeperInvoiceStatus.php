<?php
declare(strict_types=1);

namespace ThefJ\PayKeeperClient;

enum PayKeeperInvoiceStatus: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Paid = 'paid';
    case Expired = 'expired';
}