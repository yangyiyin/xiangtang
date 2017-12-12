<?php
/**
 * Created by newModule.
 * Time: 2017-12-12 09:55:05
 */
namespace Admin\Controller;

class AntCooperationController extends AdminController {
    protected $CooperationService;
    protected function _initialize() {
        parent::_initialize();
        $this->CooperationService = \Common\Service\CooperationService::get_instance();
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
        if (I('get.title')) {
            $where['title'] = ['LIKE', '%' . I('get.title') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->CooperationService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\CooperationService::$page_size);
        if($total>\Common\Service\CooperationService::$page_size){
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
            $ret = $this->CooperationService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除合作单位');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->CooperationService->get_info_by_id($id);
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
                $ret = $this->CooperationService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改合作单位');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->CooperationService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加合作单位');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {

            $cids = result_to_array($data);
            $CooperationBlockService = \Common\Service\CooperationBlockService::get_instance();
            $promotion = $CooperationBlockService->get_by_cids_type($cids, \Common\Model\NfCooperationBlockModel::TYPE_PROMOTION);
            $recommend = $CooperationBlockService->get_by_cids_type($cids, \Common\Model\NfCooperationBlockModel::TYPE_RECOMMEND);
            $promotion_cids = result_to_array($promotion, 'cid');
            $recommend_cids = result_to_array($recommend, 'cid');

            foreach ($data as $key => $_item) {


                if (in_array($_item['id'], $promotion_cids)) {
                    $data[$key]['is_promotion'] = TRUE;
                }
                if (in_array($_item['id'], $recommend_cids)) {
                    $data[$key]['is_recommend'] = TRUE;
                }

            }
        }
    }

    public function set_block() {
        $ids = I('post.ids');
        $id = I('get.id');
        $type = I('get.type');
        $CooperationBlockService = \Common\Service\CooperationBlockService::get_instance();

        if ($id) {
            $ret = $CooperationBlockService->set_block([$id], $type);
        }

        if ($ids) {
            $ret = $CooperationBlockService->set_block($ids, $type);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('批量设置活动合作单位');
        $this->success('设置成功！');
    }

    public function cancel_block() {
        $ids = I('post.ids');
        $id = I('get.id');
        $type = I('get.type');
        $CooperationBlockService = \Common\Service\CooperationBlockService::get_instance();

        if ($id) {
            $ret = $CooperationBlockService->cancel_block([$id], $type);
        }

        if ($ids) {
            $ret = $CooperationBlockService->cancel_block($ids, $type);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('批量取消活动合作单位');
        $this->success('取消成功！');
    }
}