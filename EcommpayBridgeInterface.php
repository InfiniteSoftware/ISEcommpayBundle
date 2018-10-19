<?php

declare(strict_types=1);

namespace Payum\Ecommpay;

interface EcommpayBridgeInterface
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_DECLINE = 'decline';
}
