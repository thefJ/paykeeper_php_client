<?php
declare(strict_types=1);

namespace ThefJ\PayKeeperClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ThefJ\PayKeeperClient\Exception\WrongResponseException;

class PayKeeperClient
{
    private const string TOKEN_URI = '/info/settings/token/';
    private const string INVOICE_URI = '/change/invoice/preview/';
    private const string PAYMENT_FORM_URI = '/bill/%s/';
    private const string INVOICE_STATUS_URI = '/info/invoice/byid/?id=%s';

    private const string AUTH_STRING = '%s:%s';

    private const string ABSOLUTE_URI_TEMPLATE = '%s%s';

    private ?string $token = null;

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $server,
        private readonly string $login,
        private readonly string $password,
    ) {
    }

    /**
     * @throws WrongResponseException
     * @throws GuzzleException
     */
    public function getFormLink(PayKeeperPaymentData $paymentData): string
    {
        $invoiceId = $this->generateInvoiceId($paymentData);

        return $this->getAbsolutePath(self::PAYMENT_FORM_URI, $invoiceId);
    }

    public function getInvoiceStatus(string $invoiceId): PayKeeperInvoiceStatus
    {
        $response = $this->httpClient->get($this->getAbsolutePath(self::INVOICE_STATUS_URI, $invoiceId), [
            'headers' => ['Authorization' => 'Basic ' . $this->getBasicAuthString()],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!isset($responseData['status'])) {
            throw new WrongResponseException();
        }

        return PayKeeperInvoiceStatus::from($responseData['status']);
    }

    /**
     * @throws WrongResponseException
     * @throws GuzzleException
     */
    private function generateInvoiceId(PayKeeperPaymentData $paymentData): string
    {
        $token = $this->getToken();

        $response = $this->httpClient->post($this->getAbsolutePath(self::INVOICE_URI), [
            'form_params' => [
                'pay_amount' => $paymentData->getPayAmount(),
                'clientid' => $paymentData->getClientName(),
                'orderid' => $paymentData->getOrderId(),
                'client_email' => $paymentData->getClientEmail(),
                'service_name' => $paymentData->getServiceName(),
                'client_phone' => $paymentData->getClientPhone(),
                'token' => $token,
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['invoice_id'])) {
            throw new WrongResponseException();
        }

        return (string) $responseData['invoice_id'];
    }

    /**
     * @throws GuzzleException
     * @throws WrongResponseException
     */
    private function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = $this->httpClient->get($this->getAbsolutePath(self::TOKEN_URI), [
            'headers' => ['Authorization' => 'Basic ' . $this->getBasicAuthString()],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!isset($responseData['token'])) {
            throw new WrongResponseException();
        }

        $this->token = $responseData['token'];

        return $this->token;
    }

    private function getBasicAuthString(): string
    {
        return base64_encode(sprintf(self::AUTH_STRING, $this->login, $this->password));
    }

    private function getAbsolutePath(string $uri, ?string $param = null): string
    {
        $uri = $param ? sprintf($uri, $param) : $uri;

        return sprintf(self::ABSOLUTE_URI_TEMPLATE, $this->server, $uri);
    }
}