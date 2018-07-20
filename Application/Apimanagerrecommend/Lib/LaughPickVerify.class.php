<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPickVerify extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $phone = $this->post_data['phone'];
        $code = $this->post_data['code'];

        if (!$phone || !$code) {
            return result_json(false, '对不起,您的验证码不存在!');
        }

        $type = substr($code, 0, 2);
        $service = '';
        switch ($type) {
            case \Common\Service\PageBaseService::pick_code_fightgroup:
                $service = \Common\Service\PageFightgroupService::get_instance();
                break;
            case \Common\Service\PageBaseService::pick_code_praise:
                $service = \Common\Service\PagePraiseService::get_instance();
                break;
            case \Common\Service\PageBaseService::pick_code_sign:
                $service = \Common\Service\PageSignService::get_instance();
                break;
            case \Common\Service\PageBaseService::pick_code_quick_buy:
                $service = \Common\Service\PageQuickbuyService::get_instance();
                break;
            case \Common\Service\PageBaseService::pick_code_cutprice:
                $service = \Common\Service\PageCutpriceService::get_instance();
                break;
        }

        if (!$service) {
            return result_json(false, '对不起,您的验证码不存在!');
        }

        $page_sign = $service->get_by_phone_pick_code($phone, $code);
        if (!$page_sign) {
            return result_json(false, '对不起,您的手机号或验证码有误!');
        }

        //获取标题
        $pageService = \Common\Service\PageService::get_instance();
        $page_info = $pageService->get_info_by_id($page_sign['page_id']);

        if (!$page_info) {
            return result_json(false, '对不起,您报名的活动不存在,请联系神奇店长的客服咨询相关问题!');
        }
        $page_sign['title'] = $page_info['title'];

        if ($page_sign['pick_status'] == $service::pick_status_verified) {
            return result_json(false, '该验证码已过期,店家已验证过此码,不能二次验证!', $page_sign);
        }

        if ($page_sign['pick_status'] == $service::pick_status_completed) {
            return result_json(false, '该验证码已失效,无法提货!');
        }

        return result_json(TRUE, '验证成功!', $page_sign);
    }

}