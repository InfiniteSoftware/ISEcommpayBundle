<?php

declare(strict_types=1);

namespace Payum\Ecommpay;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final class NotifyRequestValidator implements ExtensionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $paymentRepository
     */
    public function __construct(OrderRepositoryInterface $paymentRepository)
    {
        $this->orderRepository = $paymentRepository;
    }

    public function onPreExecute(Context $context): void
    {
        $previousStack = $context->getPrevious();
        $previousStackSize = count($previousStack);

        if ($previousStackSize > 1) {
            return;
        }
        if (!$context->getRequest() instanceof Notify) {
            return;
        }
        $context->getGateway()->execute($httpRequest = new GetHttpRequest());
        $context->getGateway()->execute(new Authorize(new \Payum\Core\Bridge\Spl\ArrayObject($httpRequest)));

        $order = $this->orderRepository->findOneByNumber($httpRequest->request['payment']['id']);

        if (!$order) {
            throw new BadRequestHttpException();
        }

        $context->getRequest()->setModel($this->getUnpaidPayment($order->getPayments()));
    }

    private function getUnpaidPayment($payments): PaymentInterface
    {
        $payment = null;
        foreach ($payments as $payment) {
            if (PaymentInterface::STATE_NEW === $payment->getState()) {
                return $payment;
            }
        }
        throw new HttpResponse("OK");
    }

    public function onExecute(Context $context): void
    {

    }

    public function onPostExecute(Context $context): void
    {

    }
}
