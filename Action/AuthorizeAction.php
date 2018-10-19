<?php

namespace Payum\Ecommpay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Ecommpay\Action\Api\Signer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthorizeAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $request = $model->get('request');
        $signature = $request['signature'];
        unset($request['signature']);

        $mySignature = Signer::sign($request, $this->api['secretKey']);
        if ($mySignature !== $signature) {
            throw new BadRequestHttpException();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
