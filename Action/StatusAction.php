<?php
namespace Payum\Ecommpay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Ecommpay\EcommpayBridgeInterface;

class StatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {

        ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute($status = new GetHttpRequest());

        if (isset($status->request['operation']) === false) {
            $request->markNew();
            return;
        }

        if (isset($status->request['operation']['status'])) {
            $status = $status->request['operation']['status'];

            if (EcommpayBridgeInterface::STATUS_SUCCESS === $status) {
                $request->markCaptured();
                return;
            }

            if (EcommpayBridgeInterface::STATUS_DECLINE === $status) {
                $request->markFailed();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
