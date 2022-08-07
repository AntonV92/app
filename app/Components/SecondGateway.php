<?php


namespace App\Components;

use App\Models\Payment;

/**
 * Class SecondGateway
 * @package App\Components
 */
class SecondGateway extends BaseGateway
{
    public const NAME = 'second_gateway';

    public const APP_ID = 816;
    public const APP_KEY = 'rTaasVHeteGbhwBx';

    /**
     * @return bool[]
     */
    public function processing(): array
    {
        $payload = $this->payload;

        if (!$payload->has([
            'project',
            'invoice',
            'status',
            'amount',
            'amount_paid',
            'rand'
        ])) {
            return self::ERRORS['request'];
        }

        if ($this->authorizationToken != $this->prepareSign()) {
            return self::ERRORS['signature'];
        }

        if (!$this->checkPaymentsLimit()) {
            return self::ERRORS['payments_limit'];
        }

        $payment = Payment::where('merchant_id', $payload->get('project'))
            ->where('merchant_pid', $payload->get('invoice'))
            ->where('gateway_name', self::getGatewayName())
            ->firstOrFail();

        if ($payment->amount_cents != $payload->get('amount')) {
            return self::ERRORS['amount'];
        }

        $payment->status = $payload->get('status');

        if (!$payment->save()) {
            return self::ERRORS['update_status'];
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
        $signString = $this->payload->sortKeys()->values()->join('.') . self::APP_KEY;

        return hash('md5', $signString);
    }

}
