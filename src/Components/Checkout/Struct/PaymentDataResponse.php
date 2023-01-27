<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PaymentDataResponse extends StoreApiResponse
{
    public function __construct(ArrayStruct $paymentExtension)
    {
        return parent::__construct($paymentExtension);
    }
}
