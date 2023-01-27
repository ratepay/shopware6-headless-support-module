<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Order\SalesChannel;

use Shopware\Core\Checkout\Order\SalesChannel\AbstractSetPaymentOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\SetPaymentOrderRouteResponse;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionChainProcessor;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class SetPaymentOrderRoute extends AbstractSetPaymentOrderRoute
{

    private AbstractSetPaymentOrderRoute $inner;

    private PaymentTransactionChainProcessor $paymentProcessor;

    public function __construct(AbstractSetPaymentOrderRoute  $inner, PaymentTransactionChainProcessor $paymentProcessor)
    {
        $this->inner = $inner;
        $this->paymentProcessor = $paymentProcessor;
    }

    public function getDecorated(): AbstractSetPaymentOrderRoute
    {
        return $this->inner;
    }

    public function setPayment(Request $request, SalesChannelContext $context): SetPaymentOrderRouteResponse
    {
        $rtn = $this->inner->setPayment($request, $context);

        $response = $this->paymentProcessor->process($request->get('orderId'), new RequestDataBag($request->request->all()), $context);

        return $rtn;
    }
}
