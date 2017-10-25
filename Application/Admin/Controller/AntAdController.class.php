<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntAdController extends AdminController {
    private $AdService;
    protected function _initialize() {
        parent::_initialize();
        $this->AdService = \Common\Service\AdService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.id')) {
            $where['id'] = ['EQ', I('get.id')];
        }

        if (I('get.name')) {
            $where['name'] = ['EQ', I('get.name')];
        }

        if (I('get.keyword')) {
            $where['info'] = ['LIKE', '%' . I('get.keyword') . '%'];
        }
        if (I('get.platform')) {
            $where['platform'] = ['eq', I('get.platform')];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->AdService->get_by_where($where, 'id desc', $page);
        $PageInstance = new \Think\Page($count, \Common\Service\AdService::$page_size);
        if($total>\Common\Service\AdService::$page_size){
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
            $ret = $this->AdService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除广告');
        $this->success('禁用成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $courier = $this->AdService->get_info_by_id($id);
            if ($courier) {
                $this->assign('info',$courier);
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
            if (!$data['platform']) {
                $this->error('请选择平台');
            }
            $data['platform'] = array_sum($data['platform']);
            if ($data['imgs']) {
                $data['imgs'] = join(',', $data['imgs']);
            }
            if ($id) {
                $ret = $this->AdService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改广告');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->AdService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加广告');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

}