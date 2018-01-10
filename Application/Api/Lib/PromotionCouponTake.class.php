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
        $DeductibleCouponService = \Common\Service\DeductibleCouponService::get_instance();
        $info = $DeductibleCouponService->get_info_by_id($id);
        if (!$info) {
            $this->error('没有对应的优惠券信息');
        }

        $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
        $ret = $UserDeductibleCouponService->take_one($this->uid, $id);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }
        //记录
        $one_info = $ret->data;
        $DeductibleCouponLogService = \Common\Service\DeductibleCouponLogService::get_instance();
        $data = [];
        $data['coupon_id'] = $one_info['id'];
        $data['title'] = $one_info['title'];
        $data['uid'] = $this->uid;
        $data['user_name'] = (string) $this->user_info['user_name'];
        $data['enable_time'] = current_date();
        $data['num'] = $info['num'];
        $DeductibleCouponLogService->add_one($data);

        return result_json(TRUE, '领取成功');
    }



}