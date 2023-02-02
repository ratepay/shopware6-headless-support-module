<?php

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Service;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Util\DataValidationHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ValidatorService
{

    private PaymentHandlerRegistry $paymentHandlerRegistry;

    private DataValidator $dataValidator;

    public function __construct(
        PaymentHandlerRegistry $paymentHandlerRegistry,
        DataValidator $dataValidator
    )
    {
        $this->paymentHandlerRegistry = $paymentHandlerRegistry;
        $this->dataValidator = $dataValidator;
    }

    /**
     * @param OrderEntity|SalesChannelContext $object
     * @param RequestDataBag $dataBag
     * @param bool $preValidateCheck
     * @return void
     */
    public function validate($object, RequestDataBag $dataBag, bool $preValidateCheck = false): void
    {
        if ($object instanceof SalesChannelContext) {
            $paymentMethodHandlerId = $object->getPaymentMethod()->getId();
        } elseif ($object instanceof OrderEntity) {
            /** @var OrderTransactionEntity $transaction */
            $transaction = $object->getTransactions()->last();
            if (!$transaction) {
                return;
            }

            $paymentMethodHandlerId = $transaction->getPaymentMethodId();
        } else {
            return;
        }

        $paymentMethodHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethodHandlerId);

        if (!$paymentMethodHandler instanceof AbstractPaymentHandler) {
            return;
        }

        $isPaymentDetailsWrapper = $dataBag->has('paymentDetails');
        $dataBag = $dataBag->get('paymentDetails', $dataBag);
        /** @var RequestDataBag $ratepayData */
        $ratepayData = $dataBag->get('ratepay');

        if (!$preValidateCheck || $ratepayData->getBoolean('preValidate')) {
            $validationDefinitions = $paymentMethodHandler->getValidationDefinitions($dataBag, $object);

            $subDefinitions = new DataValidationDefinition();
            DataValidationHelper::addSubConstraints($subDefinitions, $validationDefinitions);
            $definition = (new DataValidationDefinition())->addSub('ratepay', $subDefinitions);

            if ($isPaymentDetailsWrapper) {
                $definition = (new DataValidationDefinition())->addSub('paymentDetails', $definition);
                $dataBag = new RequestDataBag(['paymentDetails' => $dataBag]);
            }

            $this->dataValidator->validate($dataBag->all(), $definition);
        }
    }
}
