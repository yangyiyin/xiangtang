<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionCouponMylist extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {


        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $my_coupons = $UserDeductibleCouponService->get_by_uid($this->uid);

        $coupons = [];
        if ($my_coupons) {
            foreach ($my_coupons as $coupon) {
                $tmp = [];
                $tmp['id'] = $coupon['id'];
                $tmp['title'] = $coupon['title'];
                $tmp['code'] = $coupon['code'];
                $tmp['least'] = $coupon['least'];
                $tmp['deductible'] = $coupon['deductible'];
                $tmp['img'] = item_img($coupon['img']);
                $tmp['desc'] = '满'. format_price($coupon['least']) . '元减'. format_price($coupon['deductible']) .'元';
                
                $coupons[] = $tmp;
            }
        }

        return result_json(TRUE, '', $coupons);
    }

}