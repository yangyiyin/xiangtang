<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class PayController extends BaseController {
    public function alipay_create() {
        $this->excute_api('Api\Lib\AlipayCreate');
    }

    public function alipay_notify() {
        $this->excute_api('Api\Lib\AlipayNotify');
    }

    public function wechat_pay_create() {
        $this->excute_api('Api\Lib\WechatPayCreate');
    }
    public function wechat_pay_notify() {
        $this->excute_api('Api\Lib\WechatPayNotify');
    }
}