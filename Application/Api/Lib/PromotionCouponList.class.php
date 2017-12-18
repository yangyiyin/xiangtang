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
            foreach ($my_coupons as $key => $coupon) {
                if ($sum < $coupon['least']) {
                    continue;
                }
                $my_coupons[$key]['function'] = 'function (data){if(data["new_total_price"]>='.$coupon['least'].'){data["new_total_price"]-='.$coupon['deductible'].'; data["coupon_discount"] = data["coupon_discount"]?data["coupon_discount"]:0;data["coupon_discount"]+='.$coupon['deductible'].'}';
            }
        }
        $my_coupons = $my_coupons ? $my_coupons : [];
        $my_coupons = convert_objs($my_coupons, 'id=coupon_id,code,title,least,deductible,function');
        return result_json(TRUE, '', $my_coupons);
    }

}