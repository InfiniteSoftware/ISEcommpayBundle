# Ecommpay Payum extension


Create new project

```bash
$ composer require infinite-software/ecommpay-payum
```

To receive payment notifications, redefine class `NotifyRequestValidatorExtension` with your framework/project implementations of `orderRepository` and `PaymentInterface`.  