<?php

namespace Payum\Ecommpay;

use Payum\Ecommpay\Action\AuthorizeAction;
use Payum\Ecommpay\Action\CancelAction;
use Payum\Ecommpay\Action\ConvertPaymentAction;
use Payum\Ecommpay\Action\CaptureAction;
use Payum\Ecommpay\Action\NotifyAction;
use Payum\Ecommpay\Action\RefundAction;
use Payum\Ecommpay\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class EcommpayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'ecommpay',
            'payum.factory_title' => 'ecommpay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    'secretKey' => $config['secretKey'],
                    'projectId' => $config['projectId'],
                    'endpoint'  => 'https://paymentpage.ecommpay.com/payment?'
                ];
            };
        }
    }
}
