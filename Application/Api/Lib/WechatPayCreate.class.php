<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;

require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_config.php';
require APP_PATH . '/Common/Lib/wx_pay_sdk/wechat_api.php';


class WechatPayCreate extends BaseApi{
    private $PayService;
    private $OrderService;
    protected $method = parent::API_METHOD_POST;

    public function init() {
        $this->PayService = Service\PayService::get_instance();
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        //订单号
        //$order_id = $this->post_data['order_id'];
        $order_ids = explode(',', $this->post_data['order_ids']);
        if (!$order_ids) {
            return result_json(FALSE, '订单号不存在');
        }
        $orders = [];
        foreach ($order_ids as $order_id) {
            $ret = $this->OrderService->is_available_paying($order_id, $this->uid);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
            $orders[] = $ret->data;
        }



        $ret = $this->PayService->create_by_orders($orders);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        if ($ret->message == 'all_by_account') {//全部账户支付
            return result_json(TRUE, '', 1);
        }

        $pay_info = $ret->data;

        $wechat = new \Wechat($wechat_config);

        $response = $wechat->createPrepay($pay_info['pay_no'], $pay_info['sum']);

        result_json(TRUE, '', $response);

    }
}