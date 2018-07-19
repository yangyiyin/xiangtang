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


        $ret = curl_post_form('http://api.88plus.net/index.php/waibao/common/verify_code_manager_recommend', ['phone'=>$phone, 'code'=>$code]);

        var_dump($ret);
        return result_json(TRUE, '验证成功');
    }


}