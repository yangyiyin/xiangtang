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
        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '修改成功!');
    }

    public function uploadPicture(){

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        $this->ajaxReturn($return);
    }
}