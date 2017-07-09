<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;
use User\Api\UserApi;
class AntUserFranchiseeController extends AdminController  {
    protected $MemberService;
    protected function _initialize() {
        parent::_initialize();
        $this->MemberService = \Common\Service\MemberService::get_instance();
    }

    public function add() {


        if(IS_POST){
            /* 检测密码 */
            $password = '123123';
            /* 调用注册接口注册用户 */
            $User   =   new UserApi();
            $uid    =   $User->register($username, $password, '');
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('Member')->add($user)){
                    $this->error('加盟商添加失败！');
                } else {
                    $this->success('加盟商添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
                $this->error('加盟商添加失败!');
            }
        } else {
            $this->meta_title = '新增用户';
            $this->display();
        }


        if ($id = I('get.id')) {
            $user = $this->MemberService->get_info_by_id($id);
            if ($user) {
                $this->assign('info',$user);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');

            if (!$data['user_tel']) {
                $this->error('请输入手机号~');
            }

            if ($id) {
                $info = $this->MemberService->get_info_by_id($id);
                if ($info['user_tel'] != $data['user_tel']) { //修改用户手机号,则检测手机号是否存在
                    $ret = $this->MemberService->check_tel_available($data['user_tel']);
                    if (!$ret->success) {
                        $this->error($ret->message);
                    }
                }
                $ret = $this->MemberService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改加盟商');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                if (!is_tel_num($data['user_tel'])) {
                    $this->error('您输入的手机号码可能有误~');
                }

                //检测用户是否存在
                $ret = $this->MemberService->check_tel_available($data['user_tel']);
                if (!$ret->success) {
                    $this->error($ret->message);
                }

                $data['password_md5'] = md5('123123');

                $ret = $this->MemberService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加加盟商');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
}