<?php


namespace App\Components;

use App\Models\Payment;

/**
 * Class FirstGateway
 * @package App\Components
 */
class FirstGateway extends BaseGateway
{
    private const NAME = 'first_gateway';

    private const MERCHANT_KEY = 'KaTf5tZYHx4v7pgZ';
    private const MERCHANT_ID = 6;

    /**
     * @return string
     */
    public static function getGatewayName(): string
    {
        return self::NAME;
    }

    /**
     * @return int
     */
    public function getPaymentsLimit(): int
    {
        return parent::getPaymentsLimit();
    }

    /**
     * @return array
     */
    public function processing(): array
    {
        $payload = $this->payload;

        if (!$payload->has([
            'merchant_id',
            'payment_id',
            'status',
            'amount',
            'amount_paid',
            'timestamp',
            'sign'
        ])) {
            return [
                'status' => false,
                'message' => 'Wrong request'
            ];
        }

        if ($payload->get('sign') != $this->prepareSign()) {
            return [
                'status' => false,
                'message' => 'Wrong signature'
            ];
        }

        if (!$this->checkPaymentsLimit()) {
            return [
                'status' => false,
                'message' => 'Payments limit error'
            ];
        }

        $payment = Payment::where('merchant_id', self::MERCHANT_ID)
            ->where('merchant_pid', $payload->get('payment_id'))
            ->where('gateway_name', self::getGatewayName())
            ->firstOrFail();

        if ($payment->amount_cents != $payload->get('amount')) {
            return [
                'status' => false,
                'message' => 'Wrong payment amount'
            ];
        }

        $payment->status = $payload->get('status');

        if (!$payment->save()) {
            return [
                'status' => false,
                'message' => 'Update payment status error'
            ];
        }

        return [
            'status' => true,
        ];

    }

    /**
     * @return string
     */
    private function prepareSign(): string
    {
        $signString = $this->payload->forget('sign')->sortKeys()->values()->join(':') . self::MERCHANT_KEY;

        return hash('sha256', $signString);
    }

}
