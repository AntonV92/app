<?php


namespace App\Components;


use Illuminate\Support\Collection;

/**
 * Class GatewayFactory
 * @package App\Components
 */
class GatewayFactory
{
    /**
     * @var string
     */
    private $gatewayName;

    /**
     * GatewayFactory constructor.
     * @param string $gatewayName
     */
    public function __construct(string $gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }

    /**
     * @return BaseGateway
     */
    public function getGateway(): BaseGateway
    {
        return new ($this->getGatewaysCollection()->get($this->gatewayName));
    }

    /**
     * @return Collection
     */
    private function getGatewaysCollection(): Collection
    {
        return collect([
            FirstGateway::getGatewayName() => FirstGateway::class,
            SecondGateway::getGatewayName() => SecondGateway::class,
        ]);
    }

}
