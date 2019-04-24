<?php

declare(strict_types=1);

namespace Payum\Ecommpay;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Ecommpay\Action\NotifyAction;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

final class NotifyRequestValidatorExtension implements ExtensionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param OrderRepositoryInterface $paymentRepository
     */
    public function __construct(OrderRepositoryInterface $paymentRepository, LoggerInterface $logger)
    {
        $this->orderRepository = $paymentRepository;
        $this->logger = $logger;
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
        $context->setAction(new NotifyAction());
        $context->getGateway()->execute($httpRequest = new GetHttpRequest());
        $context->getGateway()->execute(new Authorize(new ArrayObject($httpRequest)));

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($httpRequest->request['payment']['id']);

        if (!$order) {
            $this->logger->critical('Ecommpay order not found');
            throw new BadRequestHttpException();
        }

        $context->getRequest()->setModel($this->getUnpaidPayment($order));
    }

    private function getUnpaidPayment(OrderInterface $order): PaymentInterface
    {
        $payment = null;
        foreach ($order->getPayments() as $payment) {
            if (PaymentInterface::STATE_NEW === $payment->getState()) {
                return $payment;
            }
        }
        $this->logger->critical("Ecommpay payments with state 'new' not found for order #{$order->getNumber()}");
        throw new HttpResponse("OK");
    }

    public function onExecute(Context $context): void
    {

    }

    public function onPostExecute(Context $context): void
    {

    }
}
