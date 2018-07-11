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


class WechatPayCreate extends BaseApi{
    private $PayService;
    private $OrderService;
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        //订单号
        //$order_id = $this->post_data['order_id'];
        $page_id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];
        $activity_label = $this->post_data['activity_label'];
        if (!$page_id) {
            return result_json(FALSE, '活动不存在');
        }

        //生成支付单
        $ActivityPayService = Service\ActivityPayService::get_instance();
        $ret = $ActivityPayService->create_pay($activity_label, $page_id, $this->uid, $extra_uid);
        if (!$ret) {
            return result_json(FALSE, '创建支付失败');
        }

        $pay_info = $ret;
        //
        require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';
        $wechat = new \Wechat($wechat_config);

        $response = $wechat->createPrepay($pay_info['pay_no'], $pay_info['sum'], $this->user_info['openid']);
        if ($response) {
            $ret = [];
            $ret['appid'] = $response['appid'];
            $ret['prepayid'] = $response['prepay_id'];
            $ret['noncestr'] = $response['nonce_str'];
            $ret['signType'] = 'MD5';
            $ret['timestamp'] = time();
            $ret['package'] = "prepay_id=".$ret['prepayid'];
            $ret['sign'] = $wechat->setWxSign($ret);
            ksort($ret);
        }
        result_json(TRUE, '', $ret);

    }
}