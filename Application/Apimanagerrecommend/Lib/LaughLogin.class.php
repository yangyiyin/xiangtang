<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughLogin extends BaseSapi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {


        $code = $this->post_data['code'];

        if (!$code) {
            return result_json(false, '登录参数错误');
        }
        //获取openid
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/sns/jscode2session?appid=wx979328bc70cabb2d&secret=d2e17f107d1204f6a6545662894040c0&js_code=".$code."&grant_type=authorization_code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $ret = json_decode($output,true);
        if (!$ret) {
            return result_json(false, '参数错误');
        }
        $openid = $ret['openid'];

        //查询用户信息
        $UserService = \Common\Service\UserService::get_instance();
        $user_info = $UserService->get_info_by_openid($openid);

        $UserSessionService = \Common\Service\UsersessionService::get_instance();
        if (!$user_info) {
            //创建
            $data = [];
            $data['type'] = 1;
            $data['user_name'] = 'dzdtj'.time().mt_rand(0,9);
            //$data['password_md5'] = md5($this->post_data['passwd']);
            $data['openid'] = $openid;
            $ret = $UserService->add_one($data);
            if (!$ret->success) {
                return result_json(false, '创建用户失败');
            }
            $uid = $ret->data;
        } else {
            $uid = $user_info['id'];
        }

        //默认开通vip
        $VipService = \Common\Service\VipService::get_instance();
        $VipService->extend_days($uid, 7);

        $ret = $UserSessionService->update_session_by_uid($uid);
        if (!$ret->success) {
            return result_json(false, '更新session失败');
        }

        $session = $ret->data;

        return result_json(TRUE, '', $session);
    }

}