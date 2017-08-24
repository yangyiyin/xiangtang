<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 12:18:12
 */
namespace Admin\Controller;

class AntNeedsController extends AdminController {
    protected $NeedsService;
    protected function _initialize() {
        parent::_initialize();
        $this->NeedsService = \Common\Service\NeedsService::get_instance();
    }

    public function index() {

        $NeedsTypesService = \Common\Service\NeedsTypesService::get_instance();
        $types = $NeedsTypesService->get_all_types_option(I('get.type'), ['is_normal'=>['eq',0]]);
        $this->assign('types', $types);

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

        if ($status = I('get.status')) {
            if ($status == -1) {
                $status = 0;
            }
            $where['status'] = ['EQ', $status];
        }

        if ($this->types_all) {
            $where['type'] = ['in', $this->types_all];
        }

        if (I('get.type')) {
            $where['type'] = ['EQ', I('get.type')];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->NeedsService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\NeedsService::$page_size);
        if($total>\Common\Service\NeedsService::$page_size){
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
            $ret = $this->NeedsService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除需求管理');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->NeedsService->get_info_by_id($id);
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
                $ret = $this->NeedsService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改需求管理');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->NeedsService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加需求管理');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        $types = result_to_array($data, 'type');
        $NeedsTypesService = \Common\Service\NeedsTypesService::get_instance();
        $needs_types  = $NeedsTypesService->get_by_ids($types);
        $needs_types_map = result_to_map($needs_types);
        //var_dump($needs_types_map);die();
        $new_data = [];
        foreach ($data as $da) {

            if (isset($needs_types_map[$da['type']])) {
                $da['type'] = $needs_types_map[$da['type']];
            }
            
            $new_data[] = $da;
        }

        $data = $new_data;
    }

    public function approve() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->NeedsService->approve([$id]);
        }

        if ($ids) {
            $ret = $this->NeedsService->approve($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('审核通过供求');
        $this->success('审核通过成功！');
    }



    public function reject() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->NeedsService->reject([$id]);
        }

        if ($ids) {
            $ret = $this->NeedsService->reject($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('审核拒绝供求');
        $this->success('审核拒绝成功！');
    }

    public function complete() {
        $ids = I('post.ids');
        $id = I('get.id');

        if ($id) {
            $ret = $this->NeedsService->complete([$id]);
        }

        if ($ids) {
            $ret = $this->NeedsService->complete($ids);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('完成供求');
        $this->success('完成成功！');
    }

}