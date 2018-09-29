<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;

require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';


class WechatPayCreateVip extends BaseApi{
    private $PayService;
    private $OrderService;
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        $id = $this->post_data['id'];
        if (!$id) {
            return result_json(FALSE, '请选择vip时长');
        }
        $price = $label = 0;
        foreach (Service\VipService::$price_info_list as $_price) {
            if ($_price['id'] == $id) {
                $price = $_price['price'];
                $label = $_price['time'];
            }
        }
        if (!$price) {
            return result_json(FALSE, '请选择vip时长');
        }
        //生成支付单
        $ActivityPayService = Service\ActivityPayService::get_instance();
        $ret = $ActivityPayService->create_pay_vip($this->uid, $price, $label);
        if (!$ret) {
            return result_json(FALSE, '创建支付失败');
        }

        $pay_info = $ret;
        //
//        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';
        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config_vip.php';
        $wechat = new \Wechat($wechat_config);

        $response = $wechat->createPrepay($pay_info['pay_no'], $pay_info['sum'], $this->user_info['openid']);
        if ($response) {
            $ret = [];
            $ret['appId'] = $response['appid'];
           // $ret['prepayid'] = $response['prepay_id'];
            $ret['nonceStr'] = $response['nonce_str'];
            $ret['signType'] = 'MD5';
            $ret['timeStamp'] = time();
            $ret['package'] = "prepay_id=".$response['prepay_id'];
            $ret['sign'] = $wechat->setWxSign($ret);
            ksort($ret);
        }
        $ret['pay_no'] = $pay_info['pay_no'];
        result_json(TRUE, '', $ret);

    }
}