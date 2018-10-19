<?php

namespace Payum\Ecommpay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class NotifyAction implements ActionInterface, LoggerAwareInterface
{
    use ApiAwareTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->logger->info("Ecommpay order #{$request->getFirstModel()->getOrder()->getNumber()} have paid");
        throw new HttpResponse('OK');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify
        ;
    }
}
