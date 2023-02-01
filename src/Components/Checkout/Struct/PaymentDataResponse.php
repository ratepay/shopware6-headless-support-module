<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PaymentDataResponse extends StoreApiResponse
{
    public function __construct(ArrayStruct $paymentExtension, int $status = self::HTTP_OK)
    {
        parent::__construct($paymentExtension);
        $this->setStatusCode($status);
    }
}
