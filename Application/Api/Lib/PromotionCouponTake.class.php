<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionCouponTake extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {

    }

    public function excute() {
        $id = $this->post_data['id'];

        $info = $this->DeductibleCouponService->get_info_by_id($id);
        if (!$info) {
            $this->error('没有对应的优惠券信息');
        }

        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $ret = $UserDeductibleCouponService->take_one($this->uid);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }


        return result_json(TRUE, '领取成功');
    }



}