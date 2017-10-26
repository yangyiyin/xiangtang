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

class VolunteerAlipayCreate extends BaseApi{
    private $PayService;
    private $OrderService;
    protected $method = parent::API_METHOD_POST;

    public function init() {
        $this->VolunteerService = Service\VolunteerService::get_instance();
    }

    public function excute() {
        //订单号
        //$order_id = $this->post_data['order_id'];
        $id = $this->post_data['id'];
        if (!$id) {
            return result_json(FALSE, '参数错误');
        }
        $ret = $this->VolunteerService->is_available_paying($id, $this->uid);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $info = $ret->data;

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
        $bizcontent = "{\"body\":\"app支付\","
            . "\"subject\": \"app支付\","
            . "\"out_trade_no\": \"" . $info['id'] . "\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"".($info['pay_sum']/100)."\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
        $request->setNotifyUrl("http://118.178.224.208/index.php/API/Pay/volunteer_alipay_notify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        result_json(TRUE, '', $response);
    }
}