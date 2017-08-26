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
class OrderList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        $status = I('get.type');
        $page = I('get.p', 1);
        if ($status && !$this->OrderService->is_available_status($status)) {
            return result_json(FALSE, '非法参数');
        }
        if ($status) {
            if ($status == 1) {//1是全部
                //$where['status'] = ['EQ', $status];
            } else {
                $where['status'] = ['EQ', $status];
            }
        }
        $where['uid'] = ['EQ', $this->uid];
        list($list, $count) = $this->OrderService->get_by_where($where, 'id desc', $page);
        $result = new \stdClass();

        $result->has_more = has_more($count, $page, Service\OrderService::$page_size);
        $result->list = $this->convert_data($list);

        return result_json(TRUE, '', $result);
    }

    private function convert_data($list) {
        $data = [];
        if ($list) {
            $order_ids = result_to_array($list);
            $OrderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
            $snaps = $OrderSnapshotService->get_by_order_ids($order_ids);
            $snaps_oid_map = result_to_map($snaps, 'order_id');
            foreach ($list as $_order) {
                if (!isset($snaps_oid_map[$_order['id']]['content'])) {
                    continue;
                }
                $_order['id'] = (int) $_order['id'];
                $_order['num'] = (int) $_order['num'];
                $_order['sum'] = (int) $_order['sum'];
                $_order['status'] = (int) $_order['status'];
                $_order['order_detail'] = json_decode($snaps_oid_map[$_order['id']]['content'], TRUE);
                $_order['status_desc'] = $this->OrderService->get_status_txt($_order['status']);
                $_order['to_pay'] = true;
                $_order['can_complete'] = true;
                if ($_order['pay_type'] == \Common\Model\NfOrderModel::PAY_TYPE_OFFLINE) {
                    $_order['can_complete'] = false;
                }
                $tmp = [];
                $tmp = convert_obj($_order, 'id=order_id,order_no,status,status_desc,create_time,num=total_num,sum=total_price,order_detail,to_pay,receiving_address,receiving_name,receiving_tel,receiving_service_name,receiving_service_address,receiving_type,can_complete');
                $data[] = $tmp;
            }
        }
        return $data;
    }

}