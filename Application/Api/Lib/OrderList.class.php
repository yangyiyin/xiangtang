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
        $where['status'] = ['neq', \Common\Model\NfOrderModel::STATUS_CANCEL];
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
            $sku_ids = [];
            foreach ($list as $_order) {
                if (!isset($snaps_oid_map[$_order['id']]['content'])) {
                    continue;
                }
                $_order['id'] = (int) $_order['id'];
                $_order['num'] = (int) $_order['num'];
                $_order['sum'] = (int) $_order['sum'];
                $_order['status'] = (int) $_order['status'];
                $_order['order_detail'] = json_decode($snaps_oid_map[$_order['id']]['content'], TRUE);
                $sku_ids = array_merge($sku_ids, result_to_array($_order['order_detail'], 'sku_id'));
                $_order['status_desc'] = $this->OrderService->get_status_txt($_order['status']);
                $_order['to_pay'] = true;
                $_order['can_complete'] = true;
                if ($_order['pay_type'] == \Common\Model\NfOrderModel::PAY_TYPE_OFFLINE) {
                    $_order['can_complete'] = false;
                }
                $tmp = [];
                $tmp = convert_obj($_order, 'id=order_id,type,order_no,status,status_desc,create_time,num=total_num,sum=total_price,order_detail,to_pay,receiving_address,receiving_name,receiving_tel,receiving_service_name,receiving_service_address,receiving_type,can_complete,is_real,dealer_profit');
                $data[] = $tmp;
            }

            //获取商品评论
            if ($sku_ids) {
                $sku_ids = array_unique($sku_ids);
                $ItemCommentService = \Common\Service\ItemCommentService::get_instance();
                $comments = $ItemCommentService->get_by_sku_ids_uid($sku_ids, $this->uid);
                $comments_map = result_to_map($comments,'sku_id');
                $UserService = \Common\Service\UserService::get_instance();
                foreach ($data as &$value) {
                    foreach ($value->order_detail as $key => $_item) {
                        if (isset($comments_map[$_item['sku_id']])) {
                            $value->order_detail[$key]['comment'] = $comments_map[$_item['sku_id']]['comment'];
                            $value->order_detail[$key]['has_comment'] = true;
                        } else {
                            $value->order_detail[$key]['comment'] = '';
                            $value->order_detail[$key]['has_comment'] = false;

                        }

                        if ($UserService->is_normal($value->type)) {
                            $value->order_detail[$key]['sum_dealer_profit'] = 0;
                        }
                    }
                    if ($UserService->is_normal($value->type)) {
                        $value->dealer_profit = 0;
                    }
                }


            }



        }
        return $data;
    }

}