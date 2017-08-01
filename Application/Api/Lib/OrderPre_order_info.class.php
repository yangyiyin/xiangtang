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
        $pre_order_ids = explode(',', I('get.pre_order_ids'));
        $pre_orders = $this->OrderPreService->get_by_ids($pre_order_ids);
        if (!$pre_orders) {
            return result_json(FALSE, '没有预订单信息');
        }
        $result = [];
        foreach ($pre_orders as $pre_order) {
            $pre_order_items = $this->OrderItemPreService->get_by_oid($pre_order['id']);
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $sku_ids = result_to_array($pre_order_items, 'sku_id');
            $skus = $ProductSkuService->get_by_ids($sku_ids);
            $skus_map = result_to_map($skus);
            $iids = result_to_array($pre_order_items, 'iid');
            $ItemService = Service\ItemService::get_instance();
            $items = $ItemService->get_by_ids($iids);
            $items_map  = result_to_map($items);

            //$item_num_map = result_to_map($pre_order_items, 'iid');
            $items = $this->add_item_num($pre_order_items, $skus_map);

            $items = $this->convert_data($items, $items_map, $skus_map);
            $data = [];
            $data['pre_order_id'] = (int) $pre_order['id'];
            $data['total_num'] = (int) $pre_order['num'];
            $data['total_price'] = (int) $pre_order['sum'];
            $data['item_list'] = $items;
            $result[] = $data;
        }


        return result_json(TRUE, '', $result);
    }

    private function convert_data($data, $items_map, $skus_map) {
        $list = [];
        if ($data) {
            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;
            $SkuPropertyService = Service\SkuPropertyService::get_instance();
            $sku_ids = result_to_array($skus_map);
            $sku_props = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_props_map = $SkuPropertyService->get_sku_props_map($sku_props);
            foreach ($data as $key => $_item) {

                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                }

                $_item['img'] = item_img(get_cover($items_map[$_item['iid']]['img'], 'path'));//todo 这种方式后期改掉
                $_item['num'] = (int)  $_item['num'];
                $_item['id'] = (int) $items_map[$_item['iid']]['id'];
                $_item['pid'] = (int) $items_map[$_item['iid']]['pid'];
                $_item['price'] = (int) $items_map[$_item['iid']]['price'];
                $_item['title'] = $items_map[$_item['iid']]['title'];
                $_item['desc'] =  $items_map[$_item['iid']]['desc'];
                $_item['unit_desc'] = $items_map[$_item['iid']]['unit_desc'];
                $_item['sku_id'] = (int) $skus_map[$_item['sku_id']]['id'];
                if (isset($sku_props_map[$_item['sku_id']])) {
                    $_item['props'] = $sku_props_map[$_item['sku_id']];
                }
                $list[] = convert_obj($_item, 'id=item_id,pid,sku_id,title,img,desc,unit_desc,price,num,show_price,pay_price,props');
            }

        }
        return $list;
    }

    private function add_item_num($data, $sku_map) {
        $list = [];
        if ($data) {
            foreach ($data as $key => $_item) {
                if (isset($sku_map[$_item['sku_id']]['num'])) {
                    $_item['num'] = (int) $sku_map[$_item['sku_id']]['num'];
                }
                $list[] = $_item;
            }

        }
        return $list;
    }

}