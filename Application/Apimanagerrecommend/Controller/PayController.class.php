<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class PayController extends BaseController {
    public function alipay_create() {
        $this->excute_api('Apimanagerrecommend\Lib\AlipayCreate');
    }

    public function alipay_notify() {
        $this->excute_api('Apimanagerrecommend\Lib\AlipayNotify');
    }

    public function wechat_pay_create() {
        $this->excute_api('Apimanagerrecommend\Lib\WechatPayCreate');
    }
    public function wechat_pay_notify() {
        $this->excute_api('Apimanagerrecommend\Lib\WechatPayNotify');
    }

    public function wechat_pay_create_vip() {
        $this->excute_api('Apimanagerrecommend\Lib\WechatPayCreateVip');
    }
    public function wechat_pay_notify_vip() {
        $this->excute_api('Apimanagerrecommend\Lib\WechatPayNotifyVip');
    }
}