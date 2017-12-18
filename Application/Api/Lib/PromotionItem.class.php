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
            $result['start_time'] = strtotime($itemtimelimit[0]['start_time']);
            $result['end_time'] = strtotime($itemtimelimit[0]['end_time']);

            foreach ($itemtimelimit as $timelimit) {
                $result['sku_prices'][$timelimit['sku_id']] = $timelimit['price'];
            }
        }

        return result_json(TRUE, '', $result);
    }

}