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
            if ($id) {
                $ret = $this->UserService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改经销商信息');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
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