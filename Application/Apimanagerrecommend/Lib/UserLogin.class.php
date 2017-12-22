<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class UserLogin extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $user_session = I('post.user_session');
        $user_tel = I('post.user_tel');
        $user_password = I('post.user_password');

        $user_session = $this->post_data['user_session'];
        $user_tel = $this->post_data['user_tel'];
        $user_password = $this->post_data['user_password'];

        $result = new \stdClass();
        $result->success = FALSE;
        $result->message = '登录失败';
        $result->user_session = '';
        $result->error_code = ERROR_CODE_NORMAL_ERROR;
        if ($this->callback) {
            $result->callback = $this->callback;
        }
        $UserSessionService = Service\UsersessionService::get_instance();
        if ($user_tel && $user_password) {
            $ret = $this->UserService->get_by_tel($user_tel);
            if (!$ret) {
                $result->message = '用户不存在';
                echo json_encode($result);
                exit();
            }
            if ($ret['password_md5'] != md5(base64_decode($user_password))) {
                $result->message = '密码错误';
                echo json_encode($result);
                exit();
            }
            $uid = $ret['id'];
            //成功
        } elseif ($user_session) {
            $user_session_arr = $UserSessionService->decode_user_session($user_session);
            if (is_array($user_session_arr) && count($user_session_arr) == 3) {
                list ($uid, $time, $rand_num) = $user_session_arr;
            } else {
                $result->message = '登录session异常';
                echo json_encode($result);
                exit();
            }
            $user_session_info = $UserSessionService->get_info_by_uid($uid);
            if ($user_session_info && $user_session_info['session']) {
                $UserService = Service\UserService::get_instance();
                $ret = $UserService->is_available($uid);
                if (!$ret->success) {
                    $result->message = $ret->message;
                    echo json_encode($result);
                    exit();
                }

                if ($user_session_info['session'] != $user_session) {
                    $result->message = '登录session异常';
                    echo json_encode($result);
                    exit();

                }
                //检测是否过期,默认90天
                if ((time() - $time) > 90 * 24 * 3600) {
                    $result->message = '登录信息过期,请重新登录~';
                    echo json_encode($result);
                    exit();
                }
                //成功
            } else {
                $result->message = '登录session异常';
                echo json_encode($result);
                exit();
            }

        } else {
            echo json_encode($result);
            exit;
        }

        //成功,更新session
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