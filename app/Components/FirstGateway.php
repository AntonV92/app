<?php


namespace App\Components;

use App\Models\Payment;

/**
 * Class FirstGateway
 * @package App\Components
 */
class FirstGateway extends BaseGateway
{
    protected const NAME = 'first_gateway';

    private const MERCHANT_KEY = 'KaTf5tZYHx4v7pgZ';
    private const MERCHANT_ID = 6;

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
            return self::ERRORS['request'];
        }

        if ($payload->get('sign') != $this->prepareSign()) {
            return self::ERRORS['signature'];
        }

        if (!$this->checkPaymentsLimit()) {
            return self::ERRORS['payments_limit'];
        }

        $payment = Payment::where('merchant_id', $payload->get('merchant_id'))
            ->where('merchant_pid', $payload->get('payment_id'))
            ->where('gateway_name', self::getGatewayName())
            ->firstOrFail();

        if ($payment->amount_cents != $payload->get('amount')) {
            return self::ERRORS['amount'];
        }

        $payment->status = $payload->get('status');

        if (!$payment->save()) {
            return static::ERRORS['update_status'];
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
