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
//       avatar:用户头像，
//		user_name:
//		user_tel:
//		sex:性别（男|女）
//		province：省
//		city:市
//		address:详细地址
//		password_old:原密码
//		password_new:新密码
        $data = [];
        $password_old = I('post.password_old');
        $password_new = I('post.password_new');
        $password_old = $this->post_data['password_old'];
        $password_new = $this->post_data['password_new'];

        if ($this->post_data['entity_name'])  $data['entity_name'] = $this->post_data['entity_name'];
        //if ($this->post_data['sex']) $data['sex'] = $this->post_data['sex'];
        if ($this->post_data['province']) $data['province'] = $this->post_data['province'];
        if ($this->post_data['city']) $data['city'] = $this->post_data['city'];
        if ($this->post_data['address']) $data['address'] = $this->post_data['address'];

        if ($password_old && $password_new) {
            //检测原密码
            $user_info = $this->UserService->get_info_by_id($this->uid);
            if ($user_info['password_md5'] == md5(base64_decode($password_old))) {
                $data['password_md5'] = md5(base64_decode($password_new));
            } else {
                result_json(FALSE, '原密码错误~');
            }
        }

        $ret = $this->UserService->update_by_id($this->uid, $data);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '修改成功!');
    }
}