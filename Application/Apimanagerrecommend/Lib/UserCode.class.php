<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class UserCode extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $UserCodeService;
    public function init() {
        $this->UserCodeService = Service\UserCodeService::get_instance();
    }

    public function excute() {

        $account = I('get.account');
        //验证手机号
        if (!is_tel_num($account)) {
            return result_json('FALSE', '您输入的手机号码可能有误~');
        }
        $code = $this->UserCodeService->get_info_by_tel($account);

        if ($code && (time() - strtotime($code['create_time']) < 60)) {
            return result_json('FALSE', '验证码已发出, 请稍后再试');
        }

        //产生新的验证码,并发送
        $ret = $this->UserCodeService->add_one($account);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        $code = $ret->data;
        //TODO 发送短信

        $AliyunMsmService = Service\AliyunMsmService::get_instance();
        $AliyunMsmService->run($account, ["code"=>"$code"]);

        //TODO 发送短信
        return result_json('TRUE', '发送成功', ['code'=>$code]);
    }
}