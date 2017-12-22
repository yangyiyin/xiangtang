<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class UserLoginQuick extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $user_tel = $this->post_data['user_tel'];
        $code = $this->post_data['code'];

        $result = new \stdClass();
        $result->success = FALSE;
        $result->message = '登录失败';
        $result->user_session = '';
        $result->error_code = ERROR_CODE_NORMAL_ERROR;
        if ($this->callback) {
            $result->callback = $this->callback;
        }
        if ($user_tel && $code) {
            $ret = $this->UserService->get_by_tel($user_tel);
            if (!$ret) {
                $result->message = '用户不存在';
                $result->error_code = ERROR_USER_NOT_EXIST_ERROR;
                echo json_encode($result);
                exit();
            }
            //检测code是否正确
            $UserCodeService = Service\UserCodeService::get_instance();
            $ret_code = $UserCodeService->check_code_by_tel($user_tel, $code);
            if (!$ret_code->success) {
                $result->message = $ret_code->message;
                echo json_encode($result);
                exit();
            }
            //失效code
            $UserCodeService->disable_code_by_tel($user_tel);

            $uid = $ret['id'];
            //成功
        } else {
            $result->message = '参数错误';
            echo json_encode($result);
            exit;
        }

        //成功,更新session
        $UserSessionService = Service\UsersessionService::get_instance();
        $ret = $UserSessionService->update_session_by_uid($uid);
        if (!$ret->success) {
            $result->message = $ret->message;
            echo json_encode($result);
            exit();
        }
        $result->success = TRUE;
        $result->message = '登录成功';
        $result->user_session = $ret->data;
        $result->error_code = ERROR_CODE_NONE;
        echo json_encode($result);
        exit();

    }
}