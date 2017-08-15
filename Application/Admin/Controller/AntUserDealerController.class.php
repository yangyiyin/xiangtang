<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntUserDealerController extends AntUserController {
    protected $type = 2;
    protected function _initialize() {
        parent::_initialize();
    }
    public function add() {
        if ($id = I('get.id')) {
            $user = $this->UserService->get_info_by_id($id);
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
                $info = $this->UserService->get_info_by_id($id);
                if ($info['user_tel'] != $data['user_tel']) { //修改用户手机号,则检测手机号是否存在
                    $ret = $this->UserService->check_tel_available($data['user_tel']);
                    if (!$ret->success) {
                        $this->error($ret->message);
                    }
                }
                $ret = $this->UserService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改经销商信息');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                if (!is_tel_num($data['user_tel'])) {
                    $this->error('您输入的手机号码可能有误~');
                }

                //检测用户是否存在
                $ret = $this->UserService->check_tel_available($data['user_tel']);
                if (!$ret->success) {
                    $this->error($ret->message);
                }

                $data['password_md5'] = md5('123456');
                $data['type'] = \Common\Model\NfUserModel::TYPE_DEALER;
                $data['verify_status'] = \Common\Model\NfUserModel::VERIFY_STATUS_OK;
                $data['is_inviter'] = \Common\Model\NfUserModel::IS_INVITER_SUBMIT;
                $ret = $this->UserService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加经销商');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
}