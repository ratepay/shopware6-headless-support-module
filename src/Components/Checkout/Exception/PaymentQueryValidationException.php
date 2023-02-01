<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Exception;

use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class PaymentQueryValidationException extends HttpException
{

    public function __construct(RatepayException $exception)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, '', $exception->getMessage(), [], $exception);
    }

}
