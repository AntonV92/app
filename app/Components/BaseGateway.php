<?php


namespace App\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseGateway
 * @package App\Components
 */
abstract class BaseGateway
{
    protected const ERRORS = [
        'amount' => [
            'status' => false,
            'message' => 'Wrong payment amount'
        ],
        'payments_limit' => [
            'status' => false,
            'message' => 'Payments limit error'
        ],
        'update_status' => [
            'status' => false,
            'message' => 'Update payment status error'
        ],
        'signature' => [
            'status' => false,
            'message' => 'Wrong signature'
        ],
        'request' => [
            'status' => false,
            'message' => 'Wrong request'
        ],
    ];

    /**
     * @var Collection
     */
    protected Collection $payload;

    /**
     * @var string
     */
    protected string $authorizationToken;

    /**
     * @return mixed
     */
    abstract public function processing();

    /**
     * @return string
     */
    public static function getGatewayName(): string
    {
        return static::NAME;
    }

    /**
     * @param string $token
     */
    public function setAuthorizationToken(string $token)
    {
        $this->authorizationToken = $token;
    }

    /**
     * @return int
     */
    protected function getPaymentsLimit(): int
    {
        return 0;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload)
    {
        $this->payload = collect($payload);
    }

    /**
     * @return bool
     */
    protected function checkPaymentsLimit(): bool
    {
        $paymentsLimit = $this->getPaymentsLimit();

        if ($paymentsLimit == 0) {
            return true;
        }

        $paymentsCount = DB::table('payments')
            ->where('gateway_name', static::getGatewayName())
            ->whereBetween('created_at', [date('Y-m-d 00:00'), date('Y-m-d 23:59')])
            ->count();

        if ($paymentsCount >= $paymentsLimit) {
            return false;
        }

        return true;
    }

}
