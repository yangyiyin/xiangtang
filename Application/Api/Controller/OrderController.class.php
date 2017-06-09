<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class OrderController extends BaseController {
    public function pre_order() {
        $this->excute_api('Api\Lib\OrderPre_order');
    }

    public function pre_order_info() {
        $this->excute_api('Api\Lib\OrderPre_order_info');
    }

    public function add() {
        $this->excute_api('Api\Lib\OrderAdd');
    }

    public function step() {
        $this->excute_api('Api\Lib\OrderStep');
    }

    public function _empty() {
        $this->excute_api('Api\Lib\OrderList');
    }

    public function my_orders_data() {
        $this->excute_api('Api\Lib\OrderData');
    }

}