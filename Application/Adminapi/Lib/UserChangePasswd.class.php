<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
use User\Api\UserApi;
class UserChangePasswd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {
    }

    public function excute() {
        $data = [];
        $password   =  $this->post_data['old_passwd'];
        if(empty($password)) return result_json(false, '请输入原密码');
        $data['password'] =  $this->post_data['new_passwd'];
        if (empty($data['password'])) return result_json(false, '请输入新密码');


        $Api    =   new UserApi();
        $res    =   $Api->updateInfo(UID, $password, $data);
        if($res['status']){
            return result_json(true, '修改密码成功');
        }else{

            return result_json(false, $res['info']);
        }

    }



}