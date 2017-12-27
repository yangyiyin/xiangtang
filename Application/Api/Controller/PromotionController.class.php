<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class PromotionController extends BaseController {
    public function item() {
        $this->excute_api('Api\Lib\PromotionItem');
    }

    public function overall() {
        $this->excute_api('Api\Lib\PromotionOverall');
    }

    public function coupon_list() {
        $this->excute_api('Api\Lib\PromotionCouponList');
    }
    public function coupon_public_list() {
        $this->excute_api('Api\Lib\PromotionCouponPublicList');
    }
    public function coupon_take() {
        $this->excute_api('Api\Lib\PromotionCouponTake');
    }
}