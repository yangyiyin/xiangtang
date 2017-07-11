<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntUserInviterController extends AntUserController {
    protected $type = [1,2];
    protected function _initialize() {
        parent::_initialize();
    }


    public function index() {

        $where = [];
        $where['is_inviter'] = ['IN', [\Common\Model\NfUserModel::IS_INVITER_SUBMIT, \Common\Model\NfUserModel::IS_INVITER_YES]];
        $where['type'] = ['EQ', \Common\Model\NfUserModel::TYPE_NORMAL];
        if (isset($this->type)) {
            if (is_array($this->type)) {
                $where['type'] = ['IN', $this->type];
            } else {
                $where['type'] = ['EQ', $this->type];
            }
        }

        if (I('get.id')) {
            $where['id'] = ['EQ', I('get.id')];
        }
        if (I('get.tel')) {
            $where['user_tel'] = ['EQ', I('get.tel')];
        }
        if (I('get.status')) {
            $where['is_inviter'] = ['EQ', I('get.status')];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->UserService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\UserService::$page_size);
        if($total>\Common\Service\UserService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();
        $ServicesService = \Common\Service\ServicesService::get_instance();

        $services_options = $ServicesService->get_all_option(I('get.service_id'));
        $this->assign('services_options', $services_options);

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    public function update() {
        $user_tel = I('user_tel');
        $info = $this->UserService->get_by_tel($user_tel);
        if (!$info) {
            $this->error('没有找到对应的用户');
        }
        $id = $info['id'];
        $ret = $this->UserService->can_be_inviter($id);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        $ret = $this->UserService->be_inviter([$id]);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('设置用户id:' . $id . '为分佣者');
        $this->success('设置成功！');
    }

}