<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Cart\SalesChannel;

use Ratepay\RpayPaymentsHeadless\Components\Checkout\Service\ValidatorService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartOrderRouteResponse;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartOrderRoute extends AbstractCartOrderRoute
{
    private AbstractCartOrderRoute $innerService;

    private ValidatorService $validatorService;

    public function __construct(AbstractCartOrderRoute $innerService, ValidatorService $validatorService)
    {
        $this->innerService = $innerService;
        $this->validatorService = $validatorService;
    }

    public function getDecorated(): AbstractCartOrderRoute
    {
        return $this->innerService;
    }

    public function order(Cart $cart, SalesChannelContext $context, RequestDataBag $data): CartOrderRouteResponse
    {
        $this->validatorService->validate($context, $data, true);

        return $this->innerService->order($cart, $context, $data);
    }
}
