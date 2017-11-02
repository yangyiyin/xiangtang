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
class OrderData extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        $result = new \stdClass();
        $where = [];
        $where['platform'] = $this->from;
        list($status_submit, $status_recieved, $status_sending) = $this->OrderService->get_my_status();

        $where['uid'] = ['eq', $this->uid];
        $where['status'] = ['eq', $status_submit];

        $result->submit = (int) $this->OrderService->get_count_by_where($where);
        $result->paying = $result->submit;

        $where['status'] = ['eq', \Common\Model\NfOrderModel::STATUS_PAY];
        $result->to_sending = (int) $this->OrderService->get_count_by_where($where);

        $where['status'] = ['eq', $status_sending];
        $result->to_verify = (int) $this->OrderService->get_count_by_where($where);

        $result->confirming = $result->to_sending + $result->to_verify;

        $where['status'] = ['eq', \Common\Model\NfOrderModel::STATUS_DONE];
        $result->confirmed = (int) $this->OrderService->get_count_by_where($where);

        //获取购物车的数量
        $CartService = \Common\Service\CartService::get_instance();
        $carts = $CartService->get_by_uid($this->uid, $this->from);
        $result->cart_num = 0;
        if ($carts) {
            foreach ($carts as $key => $_item) {
                $result->cart_num += $_item['num'];
            }
        }
        return result_json(TRUE, '', $result);
    }


}