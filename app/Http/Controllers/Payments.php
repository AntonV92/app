<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\GatewayFactory;

/**
 * Class Payments
 * @package App\Http\Controllers
 */
class Payments extends Controller
{
    /**
     * @param $gateway
     * @param Request $request
     * @return mixed
     */
    public function updateStatus($gateway, Request $request)
    {
        $gateway = (new GatewayFactory($gateway))->getGateway();
        $gateway->setPayload($request->post());

        if (!is_null($request->header('Authorization'))) {
            $gateway->setAuthorizationToken($request->header('Authorization'));
        }

        return $gateway->processing();
    }

}
