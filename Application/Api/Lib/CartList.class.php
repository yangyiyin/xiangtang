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
class CartList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $CartService;
    public function init() {
        $this->CartService = Service\CartService::get_instance();
    }

    public function excute() {
        $carts = $this->CartService->get_by_uid($this->uid, $this->from);
        if (!$carts) {
            $result = new \stdClass();
            $result->success = FALSE;
            $result->message = '您的购物车空空如也~';
            $result->data = NULL;
            $result->callback = I('get.callback');
            $result->error_code = 100;
            $result->total_count = 0;
            echo json_encode($result);
            exit;
        }
        $carts_iid_map = result_to_complex_map($carts, 'iid');
        $iids = result_to_array($carts, 'iid');
        $ItemService = Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        $sku_ids = result_to_array($carts, 'sku_id');
        list($result, $all_count) = $this->convert_data($items, $carts_iid_map, $sku_ids);

        $ret = new \stdClass();
        $ret->success = TRUE;
        $ret->message = '';
        $ret->data = $result;
        $ret->callback = I('get.callback');
        $ret->error_code = 0;
        $ret->total_count = $all_count;
        echo json_encode($ret);
        exit;
    }

    private function convert_data($data, $carts_iid_map, $sku_ids) {
        $list = [];
        if ($data) {
            $ItemService = Service\ItemService::get_instance();

            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;

            $cids = result_to_array($data, 'cid');
            $CategoryService = Service\CategoryService::get_instance();
            $cates = $CategoryService->get_by_ids($cids);
            $cates_cid_map = result_to_map($cates);

            $SkuPropertyService = Service\SkuPropertyService::get_instance();
            $sku_props = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_props_map = $SkuPropertyService->get_sku_props_map($sku_props);

            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $skus = $ProductSkuService->get_by_ids($sku_ids);
            $skus_map = result_to_map($skus);

            //优惠(限时抢购)
            $iids = result_to_array($data);
            $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
            $limit_activities = $ItemTimelimitActivityService->get_by_iids($iids);
            $limit_activities_map = result_to_complex_map($limit_activities, 'iid');

            $all_count = 0;
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉

                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                if ($_item['status'] != Model\NfItemModel::STATUS_NORAML) {
                    $_item['status_desc'] = $ItemService->get_status_txt($_item['status']);
                }
                $cate_name = isset($cates_cid_map[$_item['cid']]['name']) ? $cates_cid_map[$_item['cid']]['name'] : '其他';
                $list[$cate_name]['cate_name'] = $cate_name;

                if (isset($carts_iid_map[$_item['id']])) {
                    foreach ($carts_iid_map[$_item['id']] as $cart) {

                        $_item['num'] = (int) $cart['num'];
                        $all_count += $_item['num'];
                        $_item['sku_id'] = (int) $cart['sku_id'];
                        if (isset($sku_props_map[$_item['sku_id']])) {
                            $_item['props'] = $sku_props_map[$_item['sku_id']];
                        }


                        if ($UserService->is_dealer($user_info['type'])) {
                            $_item['price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                            $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                            $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['dealer_price'];
                        } elseif ($UserService->is_normal($user_info['type'])) {
                            $_item['price'] = (int) $skus_map[$_item['sku_id']]['price'];
                            $_item['show_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                            $_item['pay_price'] = (int) $skus_map[$_item['sku_id']]['price'];
                        }

                        //优惠(限时抢购)
                        if (isset($limit_activities_map[$_item['id']])) {
                            $sku_prices = $ItemTimelimitActivityService->get_price_by_info($limit_activities_map[$_item['id']], 0);
                            if ($sku_prices) {
                                if (isset($sku_prices[$_item['sku_id']]['price'])) {
                                    $_item['price'] = $_item['pay_price'] = $_item['show_price'] = $sku_prices[$_item['sku_id']]['price'];
                                }
                            }
                        }

                        $list[$cate_name]['item_list'][] = convert_obj($_item, 'id=item_id,sku_id,pid,title,img,desc,unit_desc,price,show_price,pay_price,num,status_desc,props');
                    }

                }
            }

        }
        return [array_values($list), $all_count];
    }
}