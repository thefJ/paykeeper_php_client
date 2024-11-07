<?php
declare(strict_types=1);

namespace ThefJ\PayKeeperClient\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ThefJ\PayKeeperClient\Exception\WrongResponseException;
use ThefJ\PayKeeperClient\PayKeeperClient;
use ThefJ\PayKeeperClient\PayKeeperInvoiceStatus;
use ThefJ\PayKeeperClient\PayKeeperPaymentData;

class PayKeeperClientTest extends TestCase
{
    private const string FAKE_SERVER = 'fake_server';
    private const string FAKE_LOGIN = 'fake_login';
    private const string FAKE_PASSWROD = 'fake_password';
    private const string FAKE_INVOICE_ID = 'fake_invoice_id';
    private const string FAKE_TOKEN = 'fake_token';

    #[DataProvider('provideGetFormLinkData')]
    public function testGetFormLink(string $tokenResponseData, string $invoiceResponseData, ?string $expectedLink): void
    {
        $httpClient = $this->createStub(Client::class);
        $httpClient->method('get')->willReturn($this->buildResponse($tokenResponseData));
        $httpClient->method('post')->willReturn($this->buildResponse($invoiceResponseData));
        $payKeeperClient = $this->buildPayKeeperClient($httpClient);
        $paymentData = $this->createMock(PayKeeperPaymentData::class);

        if (!$expectedLink) {
            $this->expectException(WrongResponseException::class);
        }

        $formLink = $payKeeperClient->getFormLink($paymentData);

        if ($expectedLink) {
            $this->assertEquals($expectedLink, $formLink);
        }
    }

    public static function provideGetFormLinkData(): Generator
    {
        yield ['{"token": "' . self::FAKE_TOKEN . '"}', '{"invoice_id": "' . self::FAKE_INVOICE_ID . '"}', self::FAKE_SERVER . '/bill/' . self::FAKE_INVOICE_ID . '/'];
        yield ['{}', '{"invoice_id": "' . self::FAKE_INVOICE_ID . '"}', null];
        yield ['{"token": "' . self::FAKE_TOKEN . '"}', '{}', null];
    }

    #[DataProvider('provideGetInvoceStatusData')]
    public function testGetInvoiceStatus(string $responseData, ?PayKeeperInvoiceStatus $expectedInvoiceStatus): void
    {
        $httpClient = $this->createStub(Client::class);
        $httpClient->method('get')->willReturn($this->buildResponse($responseData));
        $payKeeperClient = $this->buildPayKeeperClient($httpClient);

        if (!$expectedInvoiceStatus) {
            $this->expectException(WrongResponseException::class);
        }

        $invoiceStatus = $payKeeperClient->getInvoiceStatus(self::FAKE_INVOICE_ID);

        if ($expectedInvoiceStatus) {
            $this->assertEquals($expectedInvoiceStatus, $invoiceStatus);
        }
    }

    public static function provideGetInvoceStatusData(): Generator
    {
        yield ['{"status": "created"}', PayKeeperInvoiceStatus::Created];
        yield ['{"status": "sent"}', PayKeeperInvoiceStatus::Sent];
        yield ['{"status": "paid"}', PayKeeperInvoiceStatus::Paid];
        yield ['{"status": "expired"}', PayKeeperInvoiceStatus::Expired];
        yield ['{"test": "test"}', null];
    }

    private function buildPayKeeperClient(Client $httpClient): PayKeeperClient
    {
        return new PayKeeperClient(
            $httpClient,
            self::FAKE_SERVER,
            self::FAKE_LOGIN,
            self::FAKE_PASSWROD
        );
    }

    private function buildResponse(string $responseData): ResponseInterface
    {
        $body = $this->createStub(StreamInterface::class);
        $body->method('getContents')->willReturn($responseData);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        return $response;
    }
}