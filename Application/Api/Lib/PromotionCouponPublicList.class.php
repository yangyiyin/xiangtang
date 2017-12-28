<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class PromotionCouponPublicList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {
        $p = I('p',1);

        $DeductibleCouponService = \Common\Service\DeductibleCouponService::get_instance();
        $where = [];

        list($list, $count) = $DeductibleCouponService->get_by_where($where,'id desc',$p);

        $result = [];
        $result['list'] = $this->convert_data($list);
        $result['has_more'] = has_more($count, $p, \Common\Service\DeductibleCouponService::$page_size);


        return result_json(TRUE, '', $result);
    }

    private function convert_data($list) {
        $new_list = [];
        if ($list) {

            //获取我已领取的优惠券
            $cids = result_to_array($list);
            $UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
            $usercoupons = $UserDeductibleCouponService->get_by_cids($this->uid,$cids);
            $usercoupons_map = result_to_map($usercoupons, 'cid');
            foreach ($list as $_li) {
                $tmp = [];
                $tmp['id'] = $_li['id'];
                $tmp['title'] = $_li['title'];
                $tmp['least'] = $_li['least'];
                $tmp['deductible'] = $_li['deductible'];
                $tmp['img'] = item_img($_li['img']);
                $tmp['desc'] = '满'. format_price($_li['least']) . '元减'. format_price($_li['deductible']) .'元';
                $tmp['is_taked'] = isset($usercoupons_map[$_li['id']]) ? true : false;
                $new_list[] = $tmp;
            }
        }
        return $new_list;
    }

}