<?php
namespace App\Http\Controllers;

use App\Http\Models\Goods;
use App\Http\Models\Paypal;
use Illuminate\Http\Request;
use Response;
use Redirect;
use Cache;
use Log;

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
            'mode'           => 'sandbox',
            'log.LogEnabled' => true,
            'log.FileName'   => storage_path('logs/paypal.log'),
            'log.LogLevel'   => 'DEBUG', // 测试DEBUG，生产环境INFO
            'cache.enabled'  => true,
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
        ]);
    }

    // 创建支付
    public function create(Request $request)
    {
        $oid = $request->get('oid');
        $goods_id = $request->get('goods_id');
        $user = $request->session()->get('user');

        // 商品信息
        $goods = Goods::query()->where('id', $goods_id)->first();
        if (!$goods) {
            //TODO:购买商品页需要做判断，出现异常时挂掉
            $request->session()->flash('paypalErrorMsg', '创建支付订单失败：所购服务不存在');

            return Redirect::back();
        }

        // 设置支付信息
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // 设置所购商品信息，包含名称、数量、SKU、价格
        $item1 = new Item();
        $item1->setName($goods->name)->setCurrency('USD')->setQuantity(1)->setSku($goods->sku)->setPrice($goods->price / 100);

        $itemList = new ItemList();
        $itemList->setItems([$item1]);

        /*
        // 设定收货地址信息，防止用户自付款时可改
        $address = new ShippingAddress();
        $address->setRecipientName($user['username'])
            ->setLine1('余杭区')
            ->setLine2('文一西路969号西溪园区')
            ->setCity('杭州市')
            ->setState('浙江省')
            ->setPhone('+8613800000000')
            ->setPostalCode('311100')
            ->setCountryCode('CN');

        // 商品列表写入设定好的地址信息
        $itemList->setShippingAddress($address);
        */

        // 设置单据运费、税费、小计算
        $details = new Details();
        $details->setShipping(0)->setTax(0)->setSubtotal($goods->price / 100);

        // 设定单据金额
        $amount = new Amount();
        $amount->setCurrency("USD")->setTotal($goods->price / 100)->setDetails($details);

        // 跳转页
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(url("payment/execute?subtotal=" . $goods->price / 100))->setCancelUrl(url("payment/cancel"));

        // 设定交易描述
        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)->setDescription("购买虚拟服务")->setInvoiceNumber(uniqid());

        // 创建支付
        $payment = new Payment();
        $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction]);
        try {
            $payment->create($this->apiContext);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            Log::error('PayPal授权失败，可能是接口配置错误');

            $request->session()->flash('paypalErrorMsg', 'PayPal授权失败，可能是接口配置错误');

            return Redirect::back();
        }

        // 得到支付授权跳转页（给用户点确认付款用）
        $approvalUrl = $payment->getApprovalLink();

        return Redirect::to($approvalUrl);
    }

    // 执行支付
    public function execute(Request $request)
    {
        \Log::info('execute_params:' . var_export($request->all(), true));

        $subtotal = $request->get('subtotal');
        $paymentId = $request->get('paymentId');
        $token = $request->get('token');
        $PayerID = $request->get('PayerID');

        if (empty($paymentId) || empty($token) || empty($PayerID)) {
            $request->session()->flash('paypalErrorMsg', '支付回调地址错误');

            return Redirect::to('user/goodsList');
        } else {
            // 根据支付单据获取支付信息
            $payment = Payment::get($paymentId, $this->apiContext);

            $details = new Details();
            $details->setShipping(0)->setTax(0)->setSubtotal($subtotal);

            $amount = new Amount();
            $amount->setCurrency('USD')->setTotal($subtotal)->setDetails($details);

            $transaction = new Transaction();
            $transaction->setAmount($amount);

            // 执行支付
            $execution = new PaymentExecution();
            $execution->setPayerId($PayerID)->addTransaction($transaction);

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

    // 写入日志
    private function log($oid, $invoice_number = '', $items = '', $response_data = '', $error = '')
    {
        $paypal = new Paypal();
        $paypal->oid = $oid;
        $paypal->invoice_number = $invoice_number;
        $paypal->items = $items;
        $paypal->response_data = $response_data;
        $paypal->error = $error;
        $paypal->save();

        return $paypal->id;
    }
}