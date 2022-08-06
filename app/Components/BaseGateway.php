<?php


namespace App\Components;

use Illuminate\Support\Facades\DB;

/**
 * Class BaseGateway
 * @package App\Components
 */
abstract class BaseGateway
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @return mixed
     */
    abstract public function processing();

    /**
     * @return string
     */
    abstract public static function getGatewayName(): string;

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
    protected function setPayload(array $payload)
    {
        $this->payload = $payload;
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
            ->where('gateway_name', $this->getGatewayName())
            ->whereBetween('created_at', [date('Y-m-d 00:00'), date('Y-m-d 23:59')])
            ->count();

        if ($paymentsCount >= $this->getPaymentsLimit()) {
            return false;
        }

        return true;
    }

}
