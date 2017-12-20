<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Redirect;
use Captcha;
use Cache;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ShippingAddress;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaymentController extends Controller
{
    protected static $config;
    private $apiContext;

    function __construct()
    {
        self::$config = $this->systemConfig();

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(self::$config['paypal_client_id'], self::$config['paypal_client_secret'])
        );
        $this->apiContext->setConfig([
            'mode' => 'sandbox',
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
        ]);
    }

    // 创建支付
    public function create(Request $request)
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // 商品1
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123")
            ->setPrice(20);

        // 商品2
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("456456")
            ->setPrice(10);

        // 写入商品列表
        $itemList = new ItemList();
        $itemList->setItems([$item1, $item2]);


        // 设定收货地址信息，防止用户自付款时可改
        $address = new ShippingAddress();
        $address->setRecipientName('什么名字')
            ->setLine1('什么街什么路什么小区')
            ->setLine2('什么单元什么号')
            ->setCity('城市名')
            ->setState('浙江省')
            ->setPhone('12345678911')
            ->setPostalCode('12345')
            ->setCountryCode('CN');

        // 商品列表写入设定好的地址信息
        $itemList->setShippingAddress($address);

        // 订单详情，带入运费和税，小计
        $details = new Details();
        $details->setShipping(5)
            ->setTax(10)
            ->setSubtotal(70);

        // 设定单据金额
        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal(85)
            ->setDetails($details);

        // 设定交易描述
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("测试支付")
            ->setInvoiceNumber(uniqid());

        // 跳转页
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(url("payment/execute"))
            ->setCancelUrl(url("payment/cancel"));

        // 整个订单
        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);


// For Sample Purposes Only.
        $payment_request = clone $payment;

        // 创建支付
        try {
            $payment->create($this->apiContext);
        } catch (\Exception $ex) {
            var_dump($ex);
            exit(1);
        }

        // 得到支付授权跳转页（给用户点确认付款用）
        $approvalUrl = $payment->getApprovalLink();

        \Log::info($approvalUrl);
        \Log::info('22222'.var_export($payment_request, true));
        \Log::info('33333'.var_export($payment, true));

        return $payment;
    }

    // 执行支付
    public function execute(Request $request)
    {
        \Log::info('execute_params:'.var_export($request->all(), true));

        $paymentId = $request->get('paymentId');
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');

        // ### Approval Status
// Determine if the user approved the payment or not
\Log::info($paymentId);
\Log::info($token);
\Log::info($PayerID);
        if (empty($paymentId) || empty($token) || empty($PayerID)) {
            exit("return_url支付回调地址错误");
        } else {
            // 支付
            $payment = Payment::get($paymentId, $this->apiContext);

            // 执行支付
            $execution = new PaymentExecution();
            $execution->setPayerId($PayerID);

            $transaction = new Transaction();

            $details = new Details();
            $details->setShipping(5)->setTax(10)->setSubtotal(70);

            $amount = new Amount();
            $amount->setCurrency('USD');
            $amount->setTotal(85);
            $amount->setDetails($details);
            $transaction->setAmount($amount);

            $execution->addTransaction($transaction);

            try {
                $result = $payment->execute($execution, $this->apiContext);
                \Log::info(var_export($result, true));

                // 支付成功，写入支付单据信息

            } catch (\Exception $ex) {
                var_dump($ex);
                echo "支付失败";
                exit(1);
            }

            \Log::info(var_export($payment, true));

            return $payment;
        }
    }

    // 取消支付
    public function cancel(Request $request)
    {
        var_dump($request->all());

        echo '取消支付';
    }

    // 查询支付状态
    public function query()
    {
        
    }
}