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


        $ret = curl_post_raw('http://api.88plus.net/index.php/waibao/common/verify_code_manager_recommend', json_encode(['phone'=>$phone, 'code'=>$code]));
        $ret = json_decode($ret,true);

        if ($ret && $ret['code'] == 100) {
            return result_json(TRUE, '验证成功');
        } else {
            return result_json(false, '验证码错误');
        }

    }


}