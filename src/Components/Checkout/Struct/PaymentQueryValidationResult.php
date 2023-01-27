<?php


namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PaymentQueryValidationResult extends StoreApiResponse
{

    public static function createSuccess(string $transactionId): self
    {
        return new self(new ArrayStruct([
            'transactionId' => $transactionId
        ]));
    }

    public static function createFailed(array $errors): self
    {
        return new self(new ArrayStruct([
            'errors' => $errors
        ]));
    }
}
