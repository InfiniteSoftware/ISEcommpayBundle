<?php
namespace Payum\Ecommpay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;

class ConvertPaymentAction implements ActionInterface, ApiAwareInterface
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
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $params = [
            'payment_id' => $payment->getNumber(),
            'payment_amount' => $payment->getTotalAmount(),
            'payment_currency' => $payment->getCurrencyCode(),
            'project_id' => $this->api['projectId'],
            'customer_id' => $payment->getClientId(),
        ];
        $request->setResult($params);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
