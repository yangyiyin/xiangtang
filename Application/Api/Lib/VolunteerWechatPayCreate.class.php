<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;

require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';


class VolunteerWechatPayCreate extends BaseApi{
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
        //
        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';//todo
        $wechat = new \Wechat($wechat_config);

        $response = $wechat->createPrepay($info['id'], $info['pay_sum']);
        if ($response) {
            $ret = [];
            $ret['appid'] = $response['appid'];
            $ret['prepayid'] = $response['prepay_id'];
            $ret['noncestr'] = $response['nonce_str'];
            $ret['partnerid'] = $response['mch_id'];
            $ret['timestamp'] = time();
            $ret['package'] = "Sign=WXPay";
            $ret['sign'] = $wechat->setWxSign($ret);
            ksort($ret);
        }
        result_json(TRUE, '', json_encode($ret));

    }
}