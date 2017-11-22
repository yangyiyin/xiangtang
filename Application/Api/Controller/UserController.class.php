<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Api\Controller;
class UserController extends BaseController {
    public function regist() {
        $this->excute_api('Api\Lib\UserRegist');
    }
    public function code() {
        $this->excute_api('Api\Lib\UserCode');
    }
    public function login() {
        $this->excute_api('Api\Lib\UserLogin');
    }
    public function login_quick() {
        $this->excute_api('Api\Lib\UserLoginQuick');
    }
    public function info() {
        $this->excute_api('Api\Lib\UserInfo');
    }
    public function info_modify() {
        $this->excute_api('Api\Lib\UserInfo_modify');
    }

    public function avatar_modify() {
        $this->excute_api('Api\Lib\UserAvatar_modify');
    }

    public function extra_info() {
        $this->excute_api('Api\Lib\UserExtraInfo');
    }
    public function disabled_verify() {
        $this->excute_api('Api\Lib\UserDisabledVerify');
    }
    public function be_inviter() {
        $this->excute_api('Api\Lib\UserBeInviter');
    }
    public function my_account() {
        $this->excute_api('Api\Lib\UserMyAccount');
    }
    public function my_commission() {
        $this->excute_api('Api\Lib\UserMyCommission');
    }
    public function my_commission_detail() {
        $this->excute_api('Api\Lib\UserMyCommissionDetail');
    }
}