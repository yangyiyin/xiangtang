<?php
/**
 * Created by newModule.
 * Time: 2017-10-26 23:53:09
 */
namespace Admin\Controller;

class AntDisabledHelpController extends AdminController {
    protected $DisabledHelpService;
    protected function _initialize() {
        parent::_initialize();
        $this->DisabledHelpService = \Common\Service\DisabledHelpService::get_instance();
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
        list($data, $count) = $this->DisabledHelpService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\DisabledHelpService::$page_size);
        if($total>\Common\Service\DisabledHelpService::$page_size){
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
            $ret = $this->DisabledHelpService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除残疾人救助申请');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->DisabledHelpService->get_info_by_id($id);
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
                $ret = $this->DisabledHelpService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改残疾人救助申请');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->DisabledHelpService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加残疾人救助申请');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {
            $DisabledHelpCatService = \Common\Service\DisabledHelpCatService::get_instance();
            $all_cats = $DisabledHelpCatService->get_all();
            $all_cats_map = result_to_map($all_cats);
            foreach ($data as $key => $help) {
                $data[$key]['help_cat'] = isset($all_cats_map[$help['help_cat']]) ? $all_cats_map[$help['help_cat']] : [];
            }
        }
    }

    public function change_status() {
        $ids = I('post.ids');
        $id = I('get.id');

        $status = I('get.status');
        $status = $status ? $status : I('post.status');
        $data = ['status'=>$status];
        if ($id) {
            $ret = $this->DisabledHelpService->update_by_ids([$id], $data);
        }

        if ($ids) {
            $ret = $this->DisabledHelpService->update_by_ids($ids, $data);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('残疾人申请审核操作'.$status);
        $this->success('操作成功！');
    }


}