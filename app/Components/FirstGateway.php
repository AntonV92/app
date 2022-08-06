<?php


namespace App\Components;

/**
 * Class FirstGateway
 * @package App\Components
 */
class FirstGateway extends BaseGateway
{
    private const NAME = 'first_gateway';

    /**
     * @return string
     */
    public static function getGatewayName(): string
    {
        return self::NAME;
    }

    public function processing()
    {
        return true;
    }
}
