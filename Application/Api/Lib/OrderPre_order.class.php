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
        $order_from = $this->post_data['order_from'];
        if (!$items_num) {
            return result_json(FALSE, '参数错误~');
        }

        $items_num_arr = $items_num;

        //$items_num_arr = json_decode($items_num,true);

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

        //检测最低购买量
        $items_nums = [];
        foreach ($items_num_arr as $li) {
            $items_nums[$li['item_id']] += $li['num'];
        }
        $ret = $ItemService->check_min_limit($items_nums, $items_map);
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
                $_total_dealer_profit += $_item->num * $_item->dealer_profit;
            }

            //插入order_pre
            $data_order_pre = [];
            $data_order_pre['uid'] = $this->uid;
            $data_order_pre['seller_uid'] = $_seller_uid;
            $data_order_pre['pre_order_no'] = $order_pre_no;
            $data_order_pre['sum'] = $_total_price;
            $data_order_pre['num'] = $_total_num;
            $data_order_pre['dealer_profit'] = $_total_dealer_profit;
            $data_order_pre['order_from'] = $order_from ? $order_from : \Common\Model\NfOrderModel::FROM_DEALER;
            $data_order_pre['is_real'] =$_item->is_real;
            $data_order_pre['create_time'] = current_date();
            $data_order_pre['platform'] = $this->from;
            //运费
            $ConfService = \Common\Service\ConfService::get_instance();
            $info = $ConfService->get_by_key_name('order_freight');

            if ($info) {
                $content = json_decode($info['content'], TRUE);
                if ($data_order_pre['sum'] < $content['sum'] * 100) {
                    $data_order_pre['freight'] = $content['freight'] * 100;
                    $data_order_pre['sum'] += $data_order_pre['freight'];
                }
            }
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
                $tmp['code'] = $_item->code;
                $tmp['num'] = $_item->num;
                $tmp['sku_id'] = $_item->sku_id;
                $tmp['price'] = $_item->price;
                $tmp['sum'] = $_item->num * $_item->price;
                $tmp['sum_dealer_profit'] = $_item->num * $_item->dealer_profit;
                $tmp['promotion_type'] = $_item->promotion_type;
                $tmp['promotion_extra'] = $_item->promotion_extra;

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

            //获取产品信息
            $ProductService = \Common\Service\ProductService::get_instance();
            $pids = result_to_array($items_map, 'pid');
//            $products = $ProductService->get_by_ids($pids);
//            $products_map = result_to_map($products);

            //优惠(限时抢购)
            $iids = result_to_array($data, 'item_id');
            $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
            $limit_activities = $ItemTimelimitActivityService->get_by_iids($iids);
            $limit_activities_map = result_to_complex_map($limit_activities, 'iid');

            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($items_map[$_item['item_id']]['img'], 'path'));//todo 这种方式后期改掉

                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                    $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                }

                $_item['price'] = (int) $skus_map[$_item['sku_id']]['price']; //订单价格都按照普通价格


                //优惠(限时抢购)
                if (isset($limit_activities_map[$_item['item_id']])) {
                    $sku_prices = $ItemTimelimitActivityService->get_price_by_info($limit_activities_map[$_item['item_id']], 0);

                    if ($sku_prices) {
                        if (isset($sku_prices[$_item['sku_id']]['price'])) {

                            $_item['promotion_type'] = \Common\Model\NfOrderModel::PROMOTION_TYPE_TIMELIMIT;
                            $promotion_extra = [];
                            $promotion_extra['title'] = $items_map[$_item['item_id']]['title'];
                            if (isset($sku_props_map[$_item['sku_id']])) {
                                $promotion_extra['title'] .= $sku_props_map[$_item['sku_id']];
                            }
                            $promotion_extra['start_time'] = $limit_activities_map[$_item['item_id']][0]['start_time'];
                            $promotion_extra['end_time'] = $limit_activities_map[$_item['item_id']][0]['end_time'];
                            $promotion_extra['price'] = $_item['price'];
                            $promotion_extra['dealer_price'] = $skus_map[$_item['sku_id']]['dealer_price'];
                            $promotion_extra['timelimit_price'] = $sku_prices[$_item['sku_id']]['price'];

                            $_item['promotion_extra'] = json_encode($promotion_extra);

                            $_item['price'] = $_item['pay_price'] = $_item['show_price'] = $sku_prices[$_item['sku_id']]['price'];
                        }
                    }
                }

                $_item['dealer_profit'] = $_item['show_price'] - $_item['pay_price']; //经销商利润
                $_item['pay_price'] = $_item['show_price'];

                $_item['num'] = (int)  $_item['num'];
                $_item['id'] = (int) $_item['item_id'];
                $_item['seller_uid'] =  (int) $items_map[$_item['item_id']]['uid'];
                $_item['pid'] = (int) $items_map[$_item['item_id']]['pid'];
                //$_item['code'] = isset($products_map[$_item['pid']]['code']) ? $products_map[$_item['pid']]['code'] : '';
                $_item['code'] = $skus_map[$_item['sku_id']]['code'];
                $_item['title'] = $items_map[$_item['item_id']]['title'];
                $_item['desc'] =  $items_map[$_item['item_id']]['desc'];
                $_item['is_real'] =  $items_map[$_item['item_id']]['is_real'];
                $_item['unit_desc'] = $items_map[$_item['item_id']]['unit_desc'];
                $_item['sku_id'] = (int) $skus_map[$_item['sku_id']]['id'];


                if (isset($sku_props_map[$_item['sku_id']])) {
                    $_item['props'] = $sku_props_map[$_item['sku_id']];
                }


                $list[] = convert_obj($_item, 'id=item_id,sku_id,pid,title,img,desc,unit_desc,price,num,is_real,seller_uid,props,show_price,pay_price,dealer_profit,code,promotion_type,promotion_extra');
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