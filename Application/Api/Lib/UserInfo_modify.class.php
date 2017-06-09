<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class UserInfo_modify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $data = [];
        $password_old = I('post.password_old');
        $password_new = I('post.password_new');
        $password_old = $this->post_data['password_old'];
        $password_new = $this->post_data['password_new'];

        $user_tel = $this->post_data['user_tel'];

        if (!($password_old && $password_new) && !$user_tel) {
            result_json(FALSE, '参数不完整~');
        }
        if ($password_old && $password_new) {
            //检测原密码
            $user_info = $this->UserService->get_info_by_id($this->uid);
            if ($user_info['password_md5'] == md5(base64_decode($password_old))) {
                $data['password_md5'] = md5(base64_decode($password_new));
            } else {
                result_json(FALSE, '原密码错误~');
            }
        }

        if ($user_tel) { //修改用户注册手机
            if (!is_tel_num($user_tel)) {
                return result_json('FALSE', '您输入的手机号码可能有误~');
            }

            //检测用户是否存在
            $ret = $this->UserService->check_tel_available($user_tel);
            if (!$ret->success) {
                return result_json(FALSE, $ret->message);
            }
            $data['user_tel'] = $user_tel;
        }

        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '修改成功!');
    }
}