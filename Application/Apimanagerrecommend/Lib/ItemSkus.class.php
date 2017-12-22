<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class ItemSkus extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ProductSkuService = Service\ProductSkuService::get_instance();
    }

    public function excute() {
        $pid = I('get.pid');
        $skus = $this->ProductSkuService->get_by_pids([$pid]);
        $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
        $sku_ids = result_to_array($skus);
        $props = $SkuPropertyService->get_by_sku_ids($sku_ids);
        $prop_sku_map = result_to_complex_map($props, 'sku_id');
        $UserService = Service\UserService::get_instance();
        //获取skus
        $result_skus = [];
        foreach ($skus as $sku) {
            $temp = [];
            $temp['id'] = intval($sku['id']);
            $temp['num'] = intval($sku['num']);
            if ($UserService->is_dealer($this->user_info['type'])) {
                $temp['price'] = (int) $sku['dealer_price'];
            } elseif ($UserService->is_normal($this->user_info['type'])) {
                $temp['price'] = (int) $sku['price'];
            }

            $temp['normal_price'] = (int) $sku['price'];//这里显示高价格
            $temp['dealer_price'] = (int) $sku['dealer_price'];



            if (isset($prop_sku_map[$sku['id']])) {
                $values = result_to_array($prop_sku_map[$sku['id']], 'property_value_id');
                sort($values);
                $temp['values'] = 'v_' . join('_', $values);
            } else {
                $temp['values'] = 'default';
            }
            $result_skus[$temp['values']] = $temp;
        }
        //获取可选属性
        $result_properties = [];
        foreach ($props as $prop) {
            if (isset($result_properties[$prop['property_id']])) {
                $result_properties[$prop['property_id']]['child'][$prop['property_value_id']] = [
                    'id' => $prop['property_value_id'],
                    'name' => $prop['property_value_name']
                ];
            } else {
                $result_properties[$prop['property_id']] = [
                    'id' => $prop['property_id'],
                    'name' => $prop['property_name'],
                    'child' => [
                        $prop['property_value_id'] =>
                        [
                            'id' => $prop['property_value_id'],
                            'name' => $prop['property_value_name']
                        ]
                    ]
                ];
            }
        }
        $result_properties_new = [];
        foreach ($result_properties as $prop) {
            $temp = [];
            $temp['id'] = $prop['id'];
            $temp['name'] = $prop['name'];
            $temp['values'] = array_values($prop['child']);
            $result_properties_new[] = $temp;
        }

        $result = [
            'skus' => $result_skus,
            'properties' => $result_properties_new
        ];
        return result_json(TRUE, '', $result);
    }


}