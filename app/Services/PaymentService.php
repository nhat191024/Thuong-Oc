<?php

namespace App\Services;

use PayOS\PayOS;

class PaymentService
{
    private const MAX_ORDER_CODE = 9_007_199_254_740_991;

    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key'),
            config('services.payos.partner_code')
        );
    }

    public static function generateOrderCode(): int
    {
        return random_int(1, self::MAX_ORDER_CODE);
    }

    /**
     * Process appointment payment
     *
     * @param  string  $paymentMethod
     * @return array
     */
    public function processPayment(
        array $data,
        $paymentMethod,
        bool $isAppRequest,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
    ) {
        // This would integrate with your payment gateway
        // For now, we'll simulate payment processing

        switch ($paymentMethod) {
            case 'wallet':
                return $this->processWalletPayment($data);
            case 'credit_card':
                return $this->processCreditCardPayment($data);
            case 'qr_transfer':
                return $this->processQRTransferPayment(
                    $data['billId'],
                    $data['billCode'],
                    $data['amount'],
                    $data['buyerName'] ?? null,
                    $data['buyerEmail'] ?? null,
                    $data['buyerPhone'] ?? null,
                    $data['items'] ?? null,
                    $data['expiryTime'] ?? null,
                    $isAppRequest,
                    $returnUrl,
                    $cancelUrl,
                );
            default:
                throw new \Exception('Invalid payment method');
        }
    }

    /**
     * Process wallet payment
     */
    private function processWalletPayment($appointment)
    {
        // TODO: Implement wallet payment logic
        return [];
    }

    /**
     * Process credit card payment
     */
    private function processCreditCardPayment($appointment)
    {
        // TODO: Implement credit card payment logic
        return [];
    }

    /**
     * Process QR transfer payment
     *
     * @param  string|null  $buyerAddress
     * @return array
     */
    private function processQRTransferPayment(
        int $billId,
        string $billCode,
        int $amount,
        ?string $buyerName = null,
        ?string $buyerEmail = null,
        ?string $buyerPhone = null,
        ?array $items = null,
        ?int $expiryTime = null,
        ?bool $isAppRequest = false,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
    ) {
        $expiryTime ??= intval(now()->addMinutes(5)->timestamp);

        $url = $returnUrl;
        if (! $url) {
            $url = $isAppRequest
                ? config('services.payos.app_result_url')
                : route('payment.result');
        }

        $cancelTarget = $cancelUrl;
        if (! $cancelTarget) {
            $cancelTarget = $isAppRequest
                ? config('services.payos.app_result_url')
                : route('payment.result');
        }

        if (! $url || ! $cancelTarget) {
            throw new \RuntimeException('PayOS payment result URL is not configured.');
        }

        $checksumKey = config('services.payos.checksum_key');
        if (! $checksumKey) {
            throw new \RuntimeException('PayOS checksum key is not configured.');
        }

        $paymentRequest = [
            'orderCode' => $billId,
            'amount' => $amount,
            'description' => "VQR-{$billCode}",
            'buyerName' => $buyerName,
            'buyerEmail' => $buyerEmail,
            'buyerPhone' => $buyerPhone,
            'items' => $items,
            'cancelUrl' => $cancelTarget,
            'returnUrl' => $url,
            'expiredAt' => $expiryTime,
        ];

        $paymentRequest = array_filter($paymentRequest, function ($value) {
            return ! is_null($value) && $value !== '';
        });

        $signature = self::createSignaturePaymentRequest(
            $checksumKey,
            $paymentRequest
        );

        $paymentRequest['signature'] = $signature;

        $response = $this->payOS->createPaymentLink($paymentRequest);

        $response['url'] = $url;

        return $response;
    }

    /**
     * Create a signature for the payment request
     *
     * @param  string  $checksumKey
     * @param  array  $obj
     * @return string
     */
    public static function createSignatureFromObj($checksumKey, $obj)
    {
        ksort($obj);
        $queryStrArr = [];
        foreach ($obj as $key => $value) {
            if (in_array($value, ['undefined', 'null']) || gettype($value) == 'NULL') {
                $value = '';
            }

            if (is_array($value)) {
                $valueSortedElementObj = array_map(function ($ele) {
                    ksort($ele);

                    return $ele;
                }, $value);
                $value = json_encode($valueSortedElementObj);
            }
            $queryStrArr[] = "{$key}={$value}";
        }
        $queryStr = implode('&', $queryStrArr);
        $signature = hash_hmac('sha256', $queryStr, $checksumKey);

        return $signature;
    }

    private static function createSignaturePaymentRequest($checksumKey, $obj)
    {
        $dataStr = "amount={$obj['amount']}&cancelUrl={$obj['cancelUrl']}&description={$obj['description']}&orderCode={$obj['orderCode']}&returnUrl={$obj['returnUrl']}";
        $signature = hash_hmac('sha256', $dataStr, $checksumKey);

        return $signature;
    }
}
