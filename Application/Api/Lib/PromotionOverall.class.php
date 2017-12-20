<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionOverall extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {

        $OverallGiftActivityService = \Common\Service\OverallGiftActivityService::get_instance();
        $activities = $OverallGiftActivityService->get_all();

        if ($activities) {
            $function_part = '';
            foreach ($activities as $activity) {
                $function_part .= 'if(data["new_total_price"] >= '.$activity['least'].'){data["mall_discount_info"].push("全场满'.($activity['least']/100).'赠:'.$activity['extra'].'");}';
            }
            $function = 'function (data){data["mall_discount_info"] = [];data["new_total_price"] = data["new_total_price"]?data["new_total_price"]:data["total_price"];'.$function_part.' return data;}';
        } else {
            $function = '';
        }


        return result_json(TRUE, '', $function);
    }

}