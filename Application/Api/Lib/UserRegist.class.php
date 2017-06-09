<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class UserRegist extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $user_name = I('post.user_name');
        $user_tel = I('post.user_tel');
        $user_password = I('post.user_password');
        $user_type = I('post.user_type');
        $code = I('post.code');

        $user_name = '';
        $user_tel = $this->post_data['user_tel'];
        $user_password = $this->post_data['user_password'];
        $user_type = 1;
        $code = $this->post_data['code'];

        $result = new \stdClass();
        $result->success = FALSE;
        $result->message = '';
        $result->user_session = '';
        $result->error_code = ERROR_CODE_NONE;
        if ($this->callback) {
            $result->callback = $this->callback;
        }

        if (!$user_tel || !$user_password || !$code) {
            $result->message = '填写信息不完整~';
            echo json_encode($result);
            exit();
        }
        if (!is_tel_num($user_tel)) {
            return result_json('FALSE', '您输入的手机号码可能有误~');
        }

        //检测用户是否存在
        $ret = $this->UserService->check_tel_available($user_tel);
        if (!$ret->success) {
            $result->message = $ret->message;
            echo json_encode($result);
            exit();
        }
        //检测code是否正确
        $UserCodeService = Service\UserCodeService::get_instance();
        $ret = $UserCodeService->check_code_by_tel($user_tel, $code);
        if (!$ret->success) {
            $result->message = $ret->message;
            echo  json_encode($result);
            exit();
        }
        //失效code
        $UserCodeService->disable_code_by_tel($user_tel);

        //注册用户
        //$upload_info = upload();
        $user_password = md5(base64_decode($user_password));
        $data = [];
        $data['type'] = 1;
        $data['user_name'] = '';
        $data['user_tel'] = $user_tel;
        $data['password_md5'] = $user_password;
        $data['avatar'] = '';
        $data['entity_title'] = '';
        $data['entity_license'] = '';
        $data['entity_name'] = '';
        $data['entity_tel'] = '';
        $data['province'] = '';
        $data['city'] = '';
        $data['address'] = '';
        $data['service_id'] = $this->post_data['service_id'];
        $ret = $this->UserService->add_one($data);
        if (!$ret->success) {
            $result->message = $ret->message;
            echo  json_encode($result);
            exit();
        }
        $uid = $ret->data;

        //生成user_session
        $UsersessionService = Service\UsersessionService::get_instance();
        $ret = $UsersessionService->add_session_by_uid($uid);
        if ($ret->success) {
            $result->user_session = $ret->data;
        }
        $result->success = TRUE;
        $result->message = '注册成功!';
        echo  json_encode($result);
        exit();
    }

}