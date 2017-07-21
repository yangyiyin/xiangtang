<?php
/**
 * Created by newModule.
 * Time: 2017-07-21 10:33:25
 */
namespace Admin\Controller;

class AntItemCommentController extends AdminController {
    private $ItemCommentService;
    protected function _initialize() {
        parent::_initialize();
        $this->ItemCommentService = \Common\Service\ItemCommentService::get_instance();
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

        if (I('get.keyword')) {
            $where['comment'] = ['LIKE', '%' . I('get.keyword') . '%'];
        }

        if (I('get.id')) {
            $where['id'] = ['EQ', I('get.id')];
        }
        if (I('get.iid')) {
            $where['iid'] = ['EQ', I('get.iid')];
        }
        if (I('get.uid')) {
            $where['uid'] = ['EQ', I('get.uid')];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->ItemCommentService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ItemCommentService::$page_size);
        if($total>\Common\Service\ItemCommentService::$page_size){
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
            $ret = $this->ItemCommentService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除商品评论');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->ItemCommentService->get_info_by_id($id);
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
                $ret = $this->ItemCommentService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改商品评论');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ItemCommentService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加商品评论');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

    public function convert_data(&$data) {
        $iids = result_to_array($data, 'iid');
        $ItemService = \Common\Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        $item_map = result_to_map($items);
        $new_data = [];
        foreach ($data as $da) {

            if (isset($item_map[$da['iid']])) {
                $da['item'] = $item_map[$da['iid']];
            }
            $new_data[] = $da;
        }

        $data = $new_data;
    }

}