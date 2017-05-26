<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntCourierController extends AdminController {
    private $CourierService;
    protected function _initialize() {
        parent::_initialize();
        $this->CourierService = \Common\Service\CourierService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.sex')) {
            $where['sex'] = ['EQ', I('get.sex')];
        }


        if (I('get.tel')) {
            $where['tel'] = ['EQ', I('get.tel')];
        }
        if (I('get.IDcard')) {
            $where['IDcard'] = ['EQ', I('get.IDcard')];
        }

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->CourierService->get_by_where($where, 'id desc', $page);
        $PageInstance = new \Think\Page($count, \Common\Service\CourierService::$page_size);
        if($total>\Common\Service\CourierService::$page_size){
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
            $ret = $this->CourierService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除业务员');
        $this->success('禁用成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $courier = $this->CourierService->get_info_by_id($id);
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
            if ($id) {
                $ret = $this->CourierService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改业务员');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->CourierService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加业务员');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

}