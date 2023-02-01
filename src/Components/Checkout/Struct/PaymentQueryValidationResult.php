<?php


namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PaymentQueryValidationResult extends StoreApiResponse
{

    public function __construct(Struct $object, int $status = self::HTTP_OK)
    {
        parent::__construct($object);
        $this->setStatusCode($status);
    }

    public static function createSuccess(string $transactionId): self
    {
        return new self(new ArrayStruct([
            'transactionId' => $transactionId
        ]));
    }

    public static function createFailed(array $errors, int $status = self::HTTP_BAD_REQUEST): self
    {
        return new self(new ArrayStruct([
            'errors' => $errors
        ]), $status);
    }
}
