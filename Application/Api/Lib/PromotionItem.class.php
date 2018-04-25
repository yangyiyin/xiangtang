<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionItem extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {

        $iid = I('iid');
        if (!$iid) {
            return result_json(false, '参数错误');
        }


        //获取单品限时抢购
        $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
        $itemtimelimit = $ItemTimelimitActivityService->get_by_iid($iid);

        $result = [];
        if ($itemtimelimit) {
            $itemtimelimit_sku_map = result_to_map($itemtimelimit, 'sku_id');
            $result['start_time'] = strtotime($itemtimelimit[0]['start_time']);
            $result['end_time'] = strtotime($itemtimelimit[0]['end_time']);

            //获取商品sku
            $itemService = \Common\Service\ItemService::get_instance();
            $item_info = $itemService->get_info_by_id($iid);
            if (!$item_info) {
                return result_json(false, '商品不存在或已下架');
            }
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
            $pid = $item_info['pid'];
            $skus = $ProductSkuService->get_by_pids([$pid]);
            $sku_ids = result_to_array($skus);
            $props = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $prop_sku_map = result_to_complex_map($props, 'sku_id');

            //获取skus
            $result_skus = [];
            foreach ($skus as $sku) {
                $temp = [];
                $temp['id'] = intval($sku['id']);
                $temp['num'] = intval($sku['num']);

                //覆盖价格
                if (isset($itemtimelimit_sku_map[$sku['id']]['price'])) {
                    //$temp['price'] = $temp['normal_price'] = $temp['dealer_price'] = (int) $itemtimelimit_sku_map[$sku['id']]['price']; //去掉 2018.4.25
                }

                if (isset($prop_sku_map[$sku['id']])) {
                    $values = result_to_array($prop_sku_map[$sku['id']], 'property_value_id');
                    sort($values);
                    $temp['values'] = 'v_' . join('_', $values);
                } else {
                    $temp['values'] = 'default';
                }
                $result_skus[$temp['values']] = $temp;
            }
            $result['skus'] = $result_skus;
        }
        if (!$result) {
            $result = null;
        }
        return result_json(TRUE, '', $result);
    }

}