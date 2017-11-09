<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:31
 */
namespace Api\Lib;
use Common\Service;
class BaseApi extends Api {
    protected $uid;
    protected $user_info;
    protected $UserSessionService;
    public function __construct() {
        parent::__construct();
        $this->UserSessionService = Service\UsersessionService::get_instance();
        //检测登录信息
        $user_session = I('user_session');//get or post 均可
        if (!$user_session) {
            $user_session = $this->post_data['user_session'];
        }
        if (!$user_session) {
            result_json(FALSE, '未登录', NULL, ERROR_CODE_SESSION_ERROR);
        }
        $user_session_arr = $this->UserSessionService->decode_user_session($user_session);
        if (is_array($user_session_arr) && count($user_session_arr) == 3) {
            list ($uid, $time, $rand_num) = $user_session_arr;
        } else {
            result_json(FALSE, '登录session异常', NULL, ERROR_CODE_SESSION_ERROR);
        }
        $user_session_info = $this->UserSessionService->get_info_by_uid($uid);
        if ($user_session_info && $user_session_info['session']) {
            $UserService = Service\UserService::get_instance();
            $ret = $UserService->is_available($uid);
            if (!$ret->success) {
                result_json(FALSE, $ret->message, NULL, ERROR_CODE_SESSION_ERROR);
            }

            if ($user_session_info['session'] != $user_session) {
                result_json(FALSE, '登录session异常', NULL, ERROR_CODE_SESSION_ERROR);
            }
            //检测是否过期,默认90天
            if ((time() - $time) > 365 * 100 * 24 * 3600) {
                result_json(FALSE, '登录信息过期,请重新登录~', NULL, ERROR_CODE_SESSION_ERROR);
            }
            //成功
            $this->uid = $uid;
            $this->user_info = $ret->data;
        } else {
            result_json(FALSE, '未登录', NULL, ERROR_CODE_SESSION_ERROR);
        }
        //执行init
        $this->init();
    }
    public function init() {
        //子类实现
    }

    public function can_order() {
        if ($this->user_info && $this->user_info['verify_status'] != \Common\Model\NfUserModel::VERIFY_STATUS_OK) {
            //result_json(FALSE, '未实名认证', NULL);
        }
    }



}