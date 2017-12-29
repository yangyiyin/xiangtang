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
class UserAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {
    }

    public function excute() {
        $username = $this->post_data['username'];
        $password = $this->post_data['passwd'];
        $repassword = $this->post_data['passwd2'];
        /* 检测密码 */
        if($password != $repassword){

            return result_json(false, '密码和重复密码不一致');
        }
        /* 调用注册接口注册用户 */
        $User   =   new UserApi();
        $uid    =   $User->register($username, $password, '');
        if(0 < $uid){ //注册成功
            $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
            if(!M('Member')->add($user)){
                return result_json(false, '用户添加失败',$uid);

            } else {
                return result_json(true, '用户添加成功');
            }
        } else { //注册失败，显示错误信息
            return result_json(false, '用户添加失败',$uid);
        }


    }



}