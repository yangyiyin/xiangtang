<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionCouponList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {

        $sum = I('sum');

        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $my_coupons = $UserDeductibleCouponService->get_by_uid($this->uid);


        if ($my_coupons) {
            $new_my_coupons = [];
            foreach ($my_coupons as $key => $coupon) {
                if ($sum < $coupon['least']) {
                    continue;
                }
                $my_coupons[$key]['function'] = 'function (data){data["new_total_price"] = data["new_total_price"]?data["new_total_price"]:data["total_price"];if(data["new_total_price"]>='.$coupon['least'].'){data["new_total_price"]-='.$coupon['deductible'].'; data["coupon_discount"] = data["coupon_discount"]?data["coupon_discount"]:0;data["coupon_discount"]+='.$coupon['deductible'].'} return data;}';
                $my_coupons[$key]['desc'] = '满'.$coupon['least'].'可用';
                $new_my_coupons[] = $my_coupons[$key];
            }
        }
        $my_coupons = $new_my_coupons ? $new_my_coupons : [];
        $my_coupons = convert_objs($new_my_coupons, 'id=coupon_id,code,title,desc,least,deductible,function');
        return result_json(TRUE, '', $my_coupons);
    }

}