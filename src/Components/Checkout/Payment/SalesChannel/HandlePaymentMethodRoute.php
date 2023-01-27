<?php declare(strict_types=1);

namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Payment\SalesChannel;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPaymentsHeadless\Components\Checkout\Service\ValidatorService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractHandlePaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\HandlePaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class HandlePaymentMethodRoute extends AbstractHandlePaymentMethodRoute
{
    private AbstractHandlePaymentMethodRoute $innerService;

    private ValidatorService $validatorService;

    private EntityRepository $orderRepository;

    public function __construct(
        AbstractHandlePaymentMethodRoute $innerService,
        ValidatorService $validatorService,
        EntityRepository $orderRepository
    )
    {
        $this->innerService = $innerService;
        $this->validatorService = $validatorService;
        $this->orderRepository = $orderRepository;
    }

    public function getDecorated(): AbstractHandlePaymentMethodRoute
    {
        return $this->innerService;
    }

    public function load(Request $request, SalesChannelContext $context): HandlePaymentMethodRouteResponse
    {
        $paymentHandlerIdentifier = null;
        if ($request->request->getBoolean('updatePayment')) {
            $orderId = $request->request->get('orderId');
            // todo add validation for actual user
            /** @var OrderEntity|null $order */
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context->getContext())->first();
            /** @var OrderTransactionEntity $transaction */
            if ($order && ($transaction = $order->getTransactions()->last())) {
                $paymentHandlerIdentifier = $transaction->getPaymentMethod()->getHandlerIdentifier();
            }
        } else {
            $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();
        }

        $isRatepayMethod = $paymentHandlerIdentifier && is_subclass_of($paymentHandlerIdentifier, AbstractPaymentHandler::class);
        if ($isRatepayMethod) {
            $this->validatorService->validate($order ?? $context, new RequestDataBag($request->request->all()), false);
        }

        $response = $this->innerService->load($request, $context);

        if ($isRatepayMethod && $response->getRedirectResponse() === null) {
            // seems like, there is no error occurred. - this check does not make so much sense, but this is the only way to validate it
            $orderId = $request->get('orderId');

            $orderCriteria = new Criteria([$orderId]);
            $orderCriteria->addAssociation(OrderExtension::EXTENSION_NAME);
            /** @var OrderEntity $order */
            $order = $this->orderRepository->search($orderCriteria, $context->getContext())->first();

            /** @var RatepayOrderDataEntity $ratepayExtension */
            $ratepayExtension = $order->getExtension(OrderExtension::EXTENSION_NAME);

            return new \Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct\HandlePaymentMethodRouteResponse($response, [
                'ratepay' => [
                    RatepayOrderDataEntity::FIELD_TRANSACTION_ID => $ratepayExtension->getTransactionId(),
                    RatepayOrderDataEntity::FIELD_DESCRIPTOR => $ratepayExtension->getDescriptor()
                ]
            ]);
        }

        return $response;
    }
}
