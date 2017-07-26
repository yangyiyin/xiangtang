<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class OrderDetail extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $OrderService;
    public function init() {
        $this->OrderService = Service\OrderService::get_instance();
    }

    public function excute() {
        $order_id = I('get.order_id');

        $order = $this->OrderService->get_info_by_id($order_id);

        if (!$order) {
            return result_json(FALSE, '没有订单信息');
        }

        $OrderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
        $snaps = $OrderSnapshotService->get_by_order_ids([$order_id]);
        $snaps_oid_map = result_to_map($snaps, 'order_id');

        $order['to_pay'] = true;
        $order['id'] = (int) $order['id'];
        $order['num'] = (int) $order['num'];
        $order['sum'] = (int) $order['sum'];
        $order['status'] = (int) $order['status'];
        $order['order_detail'] = json_decode($snaps_oid_map[$order['id']]['content'], TRUE);
        $order['status_desc'] = $this->OrderService->get_status_txt($order['status']);
        $data = convert_obj($order, 'id=order_id,order_no,status,status_desc,create_time,num=total_num,sum=total_price,order_detail,to_pay');


        return result_json(TRUE, '', $data);
    }

    private function convert_data($data) {
        $list = [];
        if ($data) {


            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉

                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_normal_price'];
                }

                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                $_item['content'] = $_item['content'];
                $_item['tips'] = $_item['tips'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,content,tips');
            }

        }
        return $list;
    }
}