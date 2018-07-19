<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughSendCode extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $phone = $this->post_data['phone'];
        if (!$phone) {
            return result_json(false, '参数错误');
        }


        $ret = curl_post_form('http://api.88plus.net/index.php/waibao/common/send_code_manager_recommend', ['phone'=>$phone]);
        $ret = json_decode($ret,true);

        if ($ret && $ret['code'] == 100) {
            return result_json(TRUE, '发送成功');
        } else {
            return result_json(false, '发送失败,请稍后再试');
        }

    }


}