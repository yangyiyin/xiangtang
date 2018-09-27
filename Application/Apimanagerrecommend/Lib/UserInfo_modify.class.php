<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Model;
use Common\Service;
use Think\Upload;

class UserInfo_modify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $data = [];

        $user_name = $this->post_data['user_name'];
        if ($user_name) {
            $data['user_name'] = $user_name;
        }
        if ($this->post_data['entity_title']) {
            $data['entity_title'] = $this->post_data['entity_title'];
        }
        if ($this->post_data['entity_tel']) {
            $data['entity_tel'] = $this->post_data['entity_tel'];
        }

        if ($this->post_data['address']) {
            $data['address'] = $this->post_data['address'];
        }

        if ($data['entity_tel'] && !is_tel_num($data['entity_tel'])) {
            result_json(FALSE, '请输入正确的手机号');
        }
        if (isset($data['entity_title']) && $data['entity_title']) {
            $data['user_name'] = $data['entity_title'];
        }

        if ($this->post_data['avatarUrl']) {
            $data['avatar'] = $this->post_data['avatarUrl'];
        }

        if ($this->post_data['gender']) {
            $data['sex'] = $this->post_data['gender'];
        }
        if ($this->post_data['province']) {
            $data['province'] = $this->post_data['province'];
        }
        if ($this->post_data['city']) {
            $data['city'] = $this->post_data['city'];
        }
        if ($this->post_data['verify_status']) {
//            $data['verify_status'] = $this->post_data['verify_status'];//审核
        }

        if ($this->post_data['type']) {
            $data['type'] = $this->post_data['type'];
        }

        $return = $this->uploadPicture();
        if ($return) {
            if ($_POST['user_name']) {
                $data['user_name'] = $_POST['user_name'];
            }
            $data['avatar'] = $return[0];
        }

        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            if (strpos($ret->message, '网络繁忙') === false) {
                result_json(FALSE, $ret->message);
            } else {
               // result_json(FALSE, '网络繁忙');
            }
        }
        if ($this->post_data['verify_status'] || ($this->post_data['type'] && $this->post_data['type'] == 2)) {
//            //默认开通vip
//            $VipService = \Common\Service\VipService::get_instance();
//            $VipService->extend_days($this->uid, 7);
            result_json(TRUE, '提交成功!');
        } else {
            result_json(TRUE, '修改成功!');
        }
    }

    public function uploadPicture(){

        /* 返回标准数据 */
//        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
//
//        $Upload = new Upload();
//        $return = $Upload->upload();
        $files = [];
        if ($_FILES) {
            foreach ($_FILES as $key => $file) {
                $files[$key] = new \CURLFile(realpath($file['tmp_name']));
            }
        }
        $ret = curl_post_form('http://api.'.C('BASE_WEB_HOST').'/index.php/waibao/common/qiniu_upload?bucket=onepixel-pub', $files);
        $ret = json_decode($ret, true);
        if ($ret && isset($ret['code']) && $ret['code'] == 100) {
            return $ret['data'];
        }

        return false;
    }
}