<?php


namespace Ratepay\RpayPaymentsHeadless\Components\Checkout\Controller;

use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\PaymentQueryValidatorService;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundHttpException;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContextAndCart;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPaymentsHeadless\Components\Checkout\Exception\PaymentQueryValidationException;
use Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct\PaymentDataResponse;
use Ratepay\RpayPaymentsHeadless\Components\Checkout\Struct\PaymentQueryValidationResult;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CheckoutController extends AbstractCheckoutController
{

    private PaymentQueryValidatorService $paymentQueryValidatorService;

    private CartService $cartService;

    private TransactionIdService $transactionIdService;

    private ProfileBySalesChannelContextAndCart $profileConfigSearch;

    private ExtensionService $extensionService;

    private AccountEditOrderPageLoader $orderLoader;

    public function __construct(
        PaymentQueryValidatorService $paymentQueryValidatorService,
        CartService $cartService,
        TransactionIdService $transactionIdService,
        ProfileBySalesChannelContextAndCart $profileConfigSearch,
        ExtensionService $extensionService,
        AccountEditOrderPageLoader $orderLoader
    )
    {
        $this->paymentQueryValidatorService = $paymentQueryValidatorService;
        $this->cartService = $cartService;
        $this->transactionIdService = $transactionIdService;
        $this->profileConfigSearch = $profileConfigSearch;
        $this->extensionService = $extensionService;
        $this->orderLoader = $orderLoader;
    }

    /**
     * @Route("/store-api/ratepay/payment-query", name="store-api.ratepay.checkout.pq", methods={"POST"}, defaults={"_loginRequired"=true, "_loginRequiredAllowGuest"=true})
     */
    public function executePQ(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $profileConfigSearchObject = $this->profileConfigSearch->createSearchObject($salesChannelContext, $cart);
        $profileConfig = $profileConfigSearchObject !== null ? $this->profileConfigSearch->search($profileConfigSearchObject)->first() : null;

        try {
            if ($profileConfig === null) {
                throw new ProfileNotFoundException();
            }

            $transactionId = $this->transactionIdService->getTransactionId($salesChannelContext, TransactionIdService::PREFIX_CART, $profileConfig);

            $dataBag = new DataBag(['ratepay' => $request->request->all()]);
            $dataBag->get('ratepay')->set('profile_uuid', $profileConfig->getId());
            $this->paymentQueryValidatorService->validate($cart, $salesChannelContext, $transactionId, $dataBag);

            return (new Response())->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (ProfileNotFoundException $profileNotFoundException) {
            throw new ProfileNotFoundHttpException();
        } catch (RatepayException $ratepayException) {
            throw new PaymentQueryValidationException($ratepayException);
        }
    }

    /**
     * @Route("/store-api/ratepay/payment-data/{orderId}", name="store-api.ratepay.checkout.payment-data", methods={"GET"}, defaults={"_loginRequired"=true, "_loginRequiredAllowGuest"=true})
     */
    public function getPaymentData(Request $request, SalesChannelContext $salesChannelContext, string $orderId = null): Response
    {
        try {
            if ($orderId) {
                $subRequest = new Request();
                $subRequest->request->set('orderId', $orderId);
                $page = $this->orderLoader->load($subRequest, $salesChannelContext);
                $extension = $page->getExtension('ratepay');
            } else {
                $extension = $this->extensionService->buildPaymentDataExtension($salesChannelContext, null, $request);
            }

            return new PaymentDataResponse($extension);
        } catch (ProfileNotFoundException $profileNotFoundException) {
            throw new ProfileNotFoundHttpException();
        }
    }

    public function getDecorated(): AbstractCheckoutController
    {
        throw new DecorationPatternException(self::class);
    }
}
