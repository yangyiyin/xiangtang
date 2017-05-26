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
class OrderPre_order extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $OrderPreService;
    private $OrderItemPreService;
    public function init() {
        $this->OrderPreService = Service\OrderPreService::get_instance();
        $this->OrderItemPreService = Service\OrderItemPreService::get_instance();
    }

    public function excute() {
        $items_num = I('post.items');
        $items_num = $this->post_data['items'];
        if (!$items_num) {
            return result_json(FALSE, '参数错误~');
        }

        $items_num_arr = json_decode($items_num, TRUE);

        if (!is_array($items_num_arr)) {
            return result_json(FALSE, '参数错误~');
        }

        foreach ($items_num_arr as $item_num) {
            if (!isset($item_num['item_id']) || !isset($item_num['num'])) {
                return result_json(FALSE, '参数错误~');
            }
        }

        $iids = result_to_array($items_num_arr, 'item_id');
        $ItemService = Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        if (!$items || count($items) != count($items_num_arr)) {
            return result_json(FALSE, '参数错误~');
        }
        $item_num_map = result_to_map($items_num_arr, 'item_id');
        $items = $this->add_item_num($items, $item_num_map);
        $ret = $ItemService->check_status_stock($items);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $items = $this->convert_data($items);
        $total_num = $total_price = 0;
        foreach ($items as $_item) {
            $total_num += $_item->num;
            $total_price += $_item->num * $_item->price;
        }
        $order_pre_no = $this->OrderPreService->get_order_pre_no($this->uid);

        //插入order_pre
        $data_order_pre = [];
        $data_order_pre['uid'] = $this->uid;
        $data_order_pre['pre_order_no'] = $order_pre_no;
        $data_order_pre['sum'] = $total_price;
        $data_order_pre['num'] = $total_num;
        $data_order_pre['create_time'] = current_date();
        $ret = $this->OrderPreService->add_one($data_order_pre);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        $order_pre_id = $ret->data;

        //插入order_item_pre
        $data_order_item_pre = [];
        foreach ($items as $_item) {
            $tmp = [];
            $tmp['order_pre_id'] = $order_pre_id;
            $tmp['iid'] = $_item->item_id;
            $tmp['pid'] = $_item->pid;
            $tmp['num'] = $_item->num;
            $tmp['price'] = $_item->price;
            $tmp['sum'] = $_item->num * $_item->price;
            $data_order_item_pre[] = $tmp;
        }
        $this->OrderItemPreService->add_batch($data_order_item_pre);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $data = [];
        $data['pre_order_id'] = $order_pre_id;
        $data['total_num'] = $total_num;
        $data['total_price'] = $total_price;
        $data['item_list'] = $items;
        return result_json(TRUE, '', $data);

    }

    private function convert_data($data) {
        $list = [];
        if ($data) {
            $itemUsertypePricesService = \Common\Service\ItemUsertypePricesService::get_instance();
            $iids = result_to_array($data);
            $prices = $itemUsertypePricesService->get_by_iids($iids);
            $prices_map = result_to_complex_map($prices, 'iid');
            $UserService = Service\UserService::get_instance();
            $user_info = $UserService->get_info_by_id($this->uid);
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉
                if (isset($prices_map[$_item['id']])) {
                    $price = $itemUsertypePricesService->get_price_by_type($user_info['type'], $prices_map[$_item['id']]);
                    if ($price) {
                        $_item['price'] = $price;
                    }
                }
                $_item['num'] = (int)  $_item['num'];
                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,num');
            }

        }
        return $list;
    }

    private function add_item_num($data, $item_num_map) {
        $list = [];
        if ($data) {
            foreach ($data as $key => $_item) {
                if (isset($item_num_map[$_item['id']]['num'])) {
                    $_item['num'] = (int) $item_num_map[$_item['id']]['num'];
                }
                $list[] = $_item;
            }

        }
        return $list;
    }

}