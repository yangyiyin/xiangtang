<?php
/**
 * Created by newModule.
 * Time: 2017-08-28 09:47:41
 */
namespace Admin\Controller;

class AntAccountController extends AdminController {
    protected $AccountService;
    protected function _initialize() {
        parent::_initialize();
        $this->AccountService = \Common\Service\AccountService::get_instance();
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
        $page = I('get.p', 1);
        list($data, $count) = $this->AccountService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\AccountService::$page_size);
        if($total>\Common\Service\AccountService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    public function in_out() {
        $user_tel = '';
        $sum = 0;
        $entity_name = '';
        if ($user_tel = I('user_tel')) {
            $UserService = \Common\Service\UserService::get_instance();
            $info = $UserService->get_by_tel($user_tel);
            if (!$info) {
                $this->error('该用户不存在');
            }
            $entity_name = $info['entity_name'];
        }

        if (IS_POST) {

            $user_tel = I('user_tel');
            $sum = I('sum');
            $op = I('get.op');
            $remark = I('remark');
            if ($sum) {
                $sum *= 100;
                $AccountLogService = \Common\Service\AccountLogService::get_instance();
                if (!$op || $op == 1) {//充值
                    $ret = $this->AccountService->add_account($info['id'], $sum);
                    if (!$ret->success) {
                        $this->error($ret->message);
                    }
                    $account_data = [];
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD;
                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
                    $account_data['sum'] = $sum;
                    $account_data['oid'] = 0;
                    $account_data['op_uid'] = UID;
                    $account_data['uid'] = $info['id'];
                    $account_data['pay_no'] = '';
                    $account_data['remark'] = $remark;
                    $AccountLogService->add_one($account_data);


                } elseif ($op == 2) {//扣除
                    $ret = $this->AccountService->minus_account($info['id'], $sum);
                    if (!$ret->success) {
                        $this->error($ret->message);
                    }

                    $account_data = [];
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS;
                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
                    $account_data['sum'] = -$sum;
                    $account_data['oid'] = 0;
                    $account_data['op_uid'] = UID;
                    $account_data['uid'] = $info['id'];
                    $account_data['pay_no'] = '';
                    $account_data['remark'] = $remark;
                    $AccountLogService->add_one($account_data);

                }
                $this->success('',U('') . '/user_tel/' . $user_tel . '/op/' . $op);

            }

        }
        $account = $this->AccountService->get_info_by_uid($info['id']);
        if (isset($account['sum'])) {
            $sum = $account['sum'];
        }
        $this->assign('sum', $sum);
        $this->assign('user_tel', $user_tel);
        $this->assign('entity_name', $entity_name);
        $this->display();
    }


    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->AccountService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除佣金');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->AccountService->get_info_by_id($id);
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
                $ret = $this->AccountService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改佣金');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->AccountService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加佣金');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {

    }
}