<?php
/**
 * Created by newModule.
 * Time: 2017-12-14 09:35:06
 */
namespace Admin\Controller;

class AntUserDeductibleCouponController extends AdminController {
    protected $UserDeductibleCouponService;
    protected function _initialize() {
        parent::_initialize();
        $this->UserDeductibleCouponService = \Common\Service\UserDeductibleCouponService::get_instance();
    }

    public function index() {

        $where = [];
        /**
        if (I('get.sex')) {
            $where['sex'] = ['EQ', I('get.sex')];
        }

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        */

        if (I('get.title')) {
            $where['title'] = ['LIKE', '%' . I('get.title') . '%'];
        }

        if (I('get.id')) {
            $where['cid'] = ['EQ', I('get.id')];
        }

        if (I('get.code')) {
            $where['code'] = ['EQ', I('get.code')];
        }

        if ($user_tel = I('get.user_tel')) {
            $UserService = \Common\Service\UserService::get_instance();
            $user = $UserService->get_by_tel_all($user_tel);
            if ($user) {
                $where['uid'] = $user['id'];
            } else {
                $where['uid'] = -1;
            }
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->UserDeductibleCouponService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\UserDeductibleCouponService::$page_size);
        if($total>\Common\Service\UserDeductibleCouponService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->UserDeductibleCouponService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除抵扣优惠券');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->UserDeductibleCouponService->get_info_by_id($id);
            if ($info) {

                $this->assign('info',$info);
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
                $ret = $this->UserDeductibleCouponService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改抵扣优惠券');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->UserDeductibleCouponService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加抵扣优惠券');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {

        $uids = result_to_array($data, 'uid');
        $UserService = \Common\Service\UserService::get_instance();
        $users = $UserService->get_by_ids($uids);
        if (isset($users->success) && !$users->success) {
            $users_map = [];
        } else {
            $users_map = result_to_map($users);
        }

        $map = \Common\Model\NfUserDeductibleCouponModel::$status_map;
        foreach ($data as $key => $_da) {
            $data[$key]['status_desc'] = isset($map[$_da['status']]) ? $map[$_da['status']] : '未知';

            $data[$key]['user'] = isset($users_map[$_da['uid']]) ? $users_map[$_da['uid']] : ['user_tel'=>'平台'];
        }
    }
}