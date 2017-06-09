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
        list($status_submit, $status_recieved, $status_sending) = $this->OrderService->get_my_status();
        $where['status'] = ['eq', $status_submit];
        $where['uid'] = ['eq', $this->uid];
        $result->submit = $this->OrderService->get_count_by_where($where);
        $where['status'] = ['eq', $status_recieved];
        $result->to_sending = $this->OrderService->get_count_by_where($where);
        $where['status'] = ['eq', $status_sending];
        $result->to_verify = $this->OrderService->get_count_by_where($where);
        return result_json(TRUE, '', $result);
    }


}