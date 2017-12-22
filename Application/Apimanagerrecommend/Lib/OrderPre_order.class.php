<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
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

        $items_num_arr = $items_num;

        if (!is_array($items_num_arr)) {
            return result_json(FALSE, '参数错误~');
        }

        foreach ($items_num_arr as $item_num) {
            if (!isset($item_num['item_id']) || !isset($item_num['num']) || !isset($item_num['sku_id'])) {

                return result_json(FALSE, '参数错误~');
            }
        }

        $iids = result_to_array($items_num_arr, 'item_id');
        $ItemService = Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        if (!$items) {
            return result_json(FALSE, '参数错误~');
        }
        //$item_num_map = result_to_map($items_num_arr, 'item_id');
        //增加num字段
        //$items = $this->add_item_num($items, $item_num_map);
        $ret = $ItemService->check_status('', $items);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $ret = $ItemService->check_same_real($items);
        if (!$ret) {
            return result_json(FALSE, '商品类型不一致,虚拟商品和实物商品不能同时下单');
        }
        //检测库存
        $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
        $sku_ids = result_to_array($items_num_arr, 'sku_id');
        $skus = $ProductSkuService->get_by_ids($sku_ids);
        if (!$skus || count($skus) != count($sku_ids)) {
            return result_json(FALSE, '商品异常~');
        }
        $items_map = result_to_map($items);
        $skus_map = result_to_map($skus);
        $item_sku_num_map = result_to_map($items_num_arr, 'sku_id');
        $skus_new = [];
        foreach ($skus as $sku) {
            if (isset($item_sku_num_map[$sku['id']])) {
                $sku['buy_num'] = $item_sku_num_map[$sku['id']]['num'];
            }
            if (isset($items_map[$item_sku_num_map[$sku['id']]['item_id']])) {
                $sku['item'] = $items_map[$item_sku_num_map[$sku['id']]['item_id']];
            }
            $skus_new[] = $sku;

        }
        $ret = $ProductSkuService->check_stock($skus_new);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        //格式化,获取返回需要的字段
        $items = $this->convert_data($items_num_arr, $skus_map, $items_map);

        $items_by_sellers = result_to_complex_map($items, 'seller_uid');

        $total_num = $total_price = 0;
        foreach ($items as $_item) {
            $total_num += $_item->num;
            $total_price += $_item->num * $_item->price;
        }
        $order_pre_ids = [];
        $items_return = [];
        //print_r($items);die();
        foreach ($items_by_sellers as $_seller_uid => $_items) {
            $order_pre_no = $this->OrderPreService->get_order_pre_no($this->uid);
            $_total_num = 0;
            $_total_price = 0;
            $_total_dealer_profit = 0;
            foreach ($_items as $_item) {
                $_total_num += $_item->num;
                $_total_price += $_item->num * $_item->price;
                $_total_dealer_profit += $_item->dealer_profit;
            }

            //插入order_pre
            $data_order_pre = [];
            $data_order_pre['uid'] = $this->uid;
            $data_order_pre['seller_uid'] = $_seller_uid;
            $data_order_pre['pre_order_no'] = $order_pre_no;
            $data_order_pre['sum'] = $_total_price;
            $data_order_pre['num'] = $_total_num;
            $data_order_pre['dealer_profit'] = $_total_dealer_profit / 100;
            $data_order_pre['is_real'] =$_item->is_real;
            $data_order_pre['create_time'] = current_date();
            $ret = $this->OrderPreService->add_one($data_order_pre);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
            $order_pre_id = $ret->data;
            $order_pre_ids[] = $ret->data;
            //插入order_item_pre
            $data_order_item_pre = [];
            foreach ($_items as $_item) {
                $tmp = [];
                $tmp['order_pre_id'] = $order_pre_id;
                $tmp['iid'] = $_item->item_id;
                $tmp['pid'] = $_item->pid;
                $tmp['num'] = $_item->num;
                $tmp['sku_id'] = $_item->sku_id;
                $tmp['price'] = $_item->price;
                $tmp['sum'] = $_item->num * $_item->price;
                $data_order_item_pre[] = $tmp;
            }
            $this->OrderItemPreService->add_batch($data_order_item_pre);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }

            $items_return[] = ['pre_order_id'=> $order_pre_id, 'item_list' => $_items];

        }


        $data = [];
        $data['pre_order_ids'] = join(',', $order_pre_ids);
        $data['total_num'] = $total_num;
        $data['total_price'] = $total_price;
        $data['order_list'] = array_values($items_return);
        return result_json(TRUE, '', $data);

    }

    private function convert_data($data, $skus_map, $items_map) {
        $list = [];
        if ($data) {

            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;

            $SkuPropertyService = Service\SkuPropertyService::get_instance();
            $sku_ids = result_to_array($skus_map);
            $sku_props = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_props_map = $SkuPropertyService->get_sku_props_map($sku_props);

            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($items_map[$_item['item_id']]['img'], 'path'));//todo 这种方式后期改掉

                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                }
                $_item['price'] = (int) $skus_map[$_item['sku_id']]['price']; //订单价格都按照普通价格
                $_item['dealer_profit'] = $_item['show_price'] - $_item['pay_price']; //经销商利润
                $_item['pay_price'] = $_item['show_price'];

                $_item['num'] = (int)  $_item['num'];
                $_item['id'] = (int) $_item['item_id'];
                $_item['seller_uid'] =  (int) $items_map[$_item['item_id']]['uid'];
                $_item['pid'] = (int) $items_map[$_item['item_id']]['pid'];
                $_item['title'] = $items_map[$_item['item_id']]['title'];
                $_item['desc'] =  $items_map[$_item['item_id']]['desc'];
                $_item['is_real'] =  $items_map[$_item['item_id']]['is_real'];
                $_item['unit_desc'] = $items_map[$_item['item_id']]['unit_desc'];
                $_item['sku_id'] = (int) $skus_map[$_item['sku_id']]['id'];

                if (isset($sku_props_map[$_item['sku_id']])) {
                    $_item['props'] = $sku_props_map[$_item['sku_id']];
                }


                $list[] = convert_obj($_item, 'id=item_id,sku_id,pid,title,img,desc,unit_desc,price,num,is_real,seller_uid,props,show_price,pay_price');
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