<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Apimanagerrecommend\Controller;
class UserController extends BaseController {
    public function regist() {
        $this->excute_api('Apimanagerrecommend\Lib\UserRegist');
    }
    public function code() {
        $this->excute_api('Apimanagerrecommend\Lib\UserCode');
    }
    public function login() {
        $this->excute_api('Apimanagerrecommend\Lib\UserLogin');
    }
    public function login_quick() {
        $this->excute_api('Apimanagerrecommend\Lib\UserLoginQuick');
    }
    public function info() {
        $this->excute_api('Apimanagerrecommend\Lib\UserInfo');
    }
    public function info_modify() {
        $this->excute_api('Apimanagerrecommend\Lib\UserInfo_modify');
    }
    public function extra_info() {
        $this->excute_api('Apimanagerrecommend\Lib\UserExtraInfo');
    }
    public function disabled_verify() {
        $this->excute_api('Apimanagerrecommend\Lib\UserDisabledVerify');
    }
    public function be_inviter() {
        $this->excute_api('Apimanagerrecommend\Lib\UserBeInviter');
    }
    public function my_account() {
        $this->excute_api('Apimanagerrecommend\Lib\UserMyAccount');
    }
    public function my_commission() {
        $this->excute_api('Apimanagerrecommend\Lib\UserMyCommission');
    }
    public function my_commission_detail() {
        $this->excute_api('Apimanagerrecommend\Lib\UserMyCommissionDetail');
    }
}