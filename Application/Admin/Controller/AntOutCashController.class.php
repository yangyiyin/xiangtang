<?php
/**
 * Created by newModule.
 * Time: 2017-08-16 13:54:40
 */
namespace Admin\Controller;

class AntOutCashController extends AdminController {
    protected $OutCashService;
    protected function _initialize() {
        parent::_initialize();
        $this->OutCashService = \Common\Service\OutCashService::get_instance();
    }

    public function index() {

        $where = [];

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        if ($status = I('get.status')) {
            if ($status == -1) {
                $status = 0;
            }
            $where['status'] = ['EQ', $status];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->OutCashService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\OutCashService::$page_size);
        if($total>\Common\Service\OutCashService::$page_size){
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
            $ret = $this->OutCashService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除提现');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->OutCashService->get_info_by_id($id);
            if ($info) {

                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
        $this->display();
    }

    public function out_cash() {

        if(IS_POST) {
            $uid = I('get.uid');
            $sum = intval(I('post.sum') * 100);

            $AccountService = \Common\Service\AccountService::get_instance();
            $AccountLogService = \Common\Service\AccountLogService::get_instance();

            $ret = $AccountService->minus_account($uid, $sum);
            if (!$ret->success){
                $this->error($ret->message);
            }
            $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS;
            $account_data['sum'] = -$sum;
            $account_data['oid'] = 0;
            $account_data['op_uid'] = UID;
            $account_data['uid'] = $uid;
            $account_data['pay_no'] ='';
            $AccountLogService->add_one($account_data);
            $this->success('提现成功!');
        } else {
            $AccountService = \Common\Service\AccountService::get_instance();
            $uid = I('get.uid');
            $info = $AccountService->get_info_by_uid($uid);

            $UserService = \Common\Service\UserService::get_instance();
            $user_info = $UserService->get_info_by_id($uid);
            $this->assign('user_info', $user_info);
            $this->assign('sum', $info['sum']);
            $this->display();
        }

    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            if ($id) {
                $ret = $this->OutCashService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改提现');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->OutCashService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加提现');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {

    }

    public function approve() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->OutCashService->approve([$id]);
        }

        if ($ids) {
            $ret = $this->OutCashService->approve($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('审核通过提现');
        $this->success('审核通过成功！');
    }



    public function reject() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->OutCashService->reject([$id]);
        }

        if ($ids) {
            $ret = $this->OutCashService->reject($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('审核拒绝提现');
        $this->success('审核拒绝成功！');
    }

    public function complete() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->OutCashService->complete([$id]);
        }

        if ($ids) {
            $ret = $this->OutCashService->complete($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('完成提现');
        $this->success('完成成功！');
    }
}