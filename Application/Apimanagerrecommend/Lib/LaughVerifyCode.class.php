<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughVerifyCode extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $phone = $this->post_data['phone'];
        $code = $this->post_data['code'];
        if (!$phone || !$code) {
            return result_json(false, '参数错误');
        }

        if ($code != '123456') {
            return result_json(false, '验证码错误');
        }

//        if (!$ret->success) {
//            return result_json(false, $ret->message);
//        }
        return result_json(TRUE, '验证成功');
    }


}