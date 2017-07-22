<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/alipay/aop/AopClient.php';
require APP_PATH . '/Common/Lib/alipay/aop/request/AlipayTradeAppPayRequest.php';

class AlipayCreate extends BaseApi{
    private $PayService;
    private $OrderService;
    protected $method = parent::API_METHOD_POST;

    public function init() {
        $this->PayService = Service\PayService::get_instance();
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        //订单号
        $order_id = $this->post_data['order_id'];
        if (!$order_id) {
            return result_json(FALSE, '订单号不存在');
        }
        $ret = $this->OrderService->is_available_paying($order_id, $this->uid);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        $order = $ret->data;


        $ret = $this->PayService->create_by_order($order);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        $pay_info = $ret->data;

        $aop = new \AopClient;
        //$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";
        $aop->appId = Service\PayService::AlipayAppId;
        $aop->rsaPrivateKey = Service\PayService::AlipayPriKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = Service\PayService::AlipayPubKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"蚂蚁app支付\","
            . "\"subject\": \"蚂蚁app支付\","
            . "\"out_trade_no\": \"" . $pay_info['pay_no'] . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".($pay_info['sum'] / 100)."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl("http://php.gooduo.net/project_ant/index.php/API/Pay/alipay_notify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        result_json(TRUE, '', $response);
    }
}