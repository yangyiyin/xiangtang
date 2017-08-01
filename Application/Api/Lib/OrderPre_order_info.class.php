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
class OrderPre_order_info extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $OrderPreService;
    private $OrderItemPreService;
    public function init() {
        $this->OrderPreService = Service\OrderPreService::get_instance();
        $this->OrderItemPreService = Service\OrderItemPreService::get_instance();
    }

    public function excute() {
        $pre_order_id = I('get.pre_order_id');
        $pre_order = $this->OrderPreService->get_info_by_id($pre_order_id);
        if (!$pre_order) {
            return result_json(FALSE, '没有预订单信息');
        }

        $pre_order_items = $this->OrderItemPreService->get_by_oid($pre_order['id']);

        $iids = result_to_array($pre_order_items, 'iid');
        $ItemService = Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        $item_num_map = result_to_map($pre_order_items, 'iid');
        $items = $this->add_item_num($items, $item_num_map);

        $items = $this->convert_data($items);
        $data = [];
        $data['pre_order_id'] = (int) $pre_order['id'];
        $data['total_num'] = (int) $pre_order['num'];
        $data['total_price'] = (int) $pre_order['sum'];
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