<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
use Think\Upload;
class UserAvatar_modify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $data = [];


        $return = $this->uploadPicture();
        if ($return) {
            $data['avatar'] = '/Uploads/' . $return['avatar']['savepath'] . $return['avatar']['savename'];
            //$data['avatar'] = json_encode($_POST).'1';
        } else {
            result_json(FALSE, '上传数据有误!');
        }


        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
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