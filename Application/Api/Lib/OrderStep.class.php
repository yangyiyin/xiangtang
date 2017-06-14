<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class OrderStep extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        $this->can_order();
        $order_id = I('post.order_id');
        $action = I('post.action');
        $order_id = $this->post_data['order_id'];
        $action = $this->post_data['action'];

        if (!$order_id) {
            return result_json(FALSE, '参数错误~');
        }

        $ret = $this->OrderService->process($order_id, $action, $this->uid);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '操作成功~');
    }
}