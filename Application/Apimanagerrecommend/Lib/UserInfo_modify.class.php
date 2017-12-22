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

//        var_dump($this->post);
//        var_dump($_FILES);die();





//        if (!$user_name) {
//            result_json(FALSE, '参数不完整~');
//        }

        $data['user_name'] = $user_name;
        $return = $this->uploadPicture();
        if ($return) {
            $data['user_name'] = $_POST['user_name'];
            $data['avatar'] = '/Uploads/' . $return['avatar']['savepath'] . $return['avatar']['savename'];
            //$data['avatar'] = json_encode($_POST).'1';
        }

        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            if (strpos($ret->message, '网络繁忙') === false) {
                result_json(FALSE, $ret->message);
            } else {
                result_json(FALSE, '网络繁忙');
            }
        }
        result_json(TRUE, '修改成功!');
    }

    public function uploadPicture(){

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        $Upload = new Upload();
        $return = $Upload->upload();
        return $return;
    }
}