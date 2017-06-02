<?php
/**
 * Created by newModule.
 * Time: 2017-06-01 17:33:07
 */
namespace Admin\Controller;

class AntServicesController extends AdminController {
    private $ServicesService;
    protected function _initialize() {
        parent::_initialize();
        $this->ServicesService = \Common\Service\ServicesService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.title')) {
            $where['title'] = ['LIKE', '%' . I('get.title') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->ServicesService->get_by_where($where, 'id desc', $page);
        $PageInstance = new \Think\Page($count, \Common\Service\ServicesService::$page_size);
        if($total>\Common\Service\ServicesService::$page_size){
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
            $ret = $this->ServicesService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除网点');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->ServicesService->get_info_by_id($id);
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
                $ret = $this->ServicesService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改网点');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ServicesService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加网点');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

}