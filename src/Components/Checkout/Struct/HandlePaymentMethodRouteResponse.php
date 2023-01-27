<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct;

use \Shopware\Core\Checkout\Payment\SalesChannel\HandlePaymentMethodRouteResponse as CoreHandlePaymentMethodRouteResponse;
use Shopware\Core\Framework\Struct\Struct;

class HandlePaymentMethodRouteResponse extends CoreHandlePaymentMethodRouteResponse
{

    public function __construct(CoreHandlePaymentMethodRouteResponse $response, array $additionalData)
    {
        parent::__construct($response->getRedirectResponse());
        $this->object->assign($additionalData);
    }

    public function getObject(): Struct
    {
        return $this->object;
    }

}
