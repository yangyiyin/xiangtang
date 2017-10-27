<?php
/**
 * Created by newModule.
 * Time: 2017-10-27 10:55:01
 */
namespace Admin\Controller;

class AntDisabledManController extends AdminController {
    protected $DisabledManService;
    protected function _initialize() {
        parent::_initialize();
        $this->DisabledManService = \Common\Service\DisabledManService::get_instance();
    }

    public function index() {

        $where = [];

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->DisabledManService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\DisabledManService::$page_size);
        if($total>\Common\Service\DisabledManService::$page_size){
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
            $ret = $this->DisabledManService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除残疾人');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->DisabledManService->get_info_by_id($id);
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

            if (!$data['name'] || !$data['tel'] || !$data['address'] || !$data['id_no'] || !$data['content']) {
                $this->error('请填写相关必填项!');
            }
            if ($id) {
                $ret = $this->DisabledManService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改残疾人');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->DisabledManService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加残疾人');
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