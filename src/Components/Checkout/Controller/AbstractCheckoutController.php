<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCheckoutController
{
    abstract public function getDecorated(): AbstractCheckoutController;

    abstract public function executePQ(Request $request, SalesChannelContext $salesChannelContext): Response;
}
