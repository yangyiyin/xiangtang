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
}