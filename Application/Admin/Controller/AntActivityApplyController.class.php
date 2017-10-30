<?php
/**
 * Created by newModule.
 * Time: 2017-10-30 13:47:59
 */
namespace Admin\Controller;

class AntActivityApplyController extends AdminController {
    protected $ActivityApplyService;
    protected function _initialize() {
        parent::_initialize();
        $this->ActivityApplyService = \Common\Service\ActivityApplyService::get_instance();
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
        list($data, $count) = $this->ActivityApplyService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ActivityApplyService::$page_size);
        if($total>\Common\Service\ActivityApplyService::$page_size){
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
            $ret = $this->ActivityApplyService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除公益活动报名');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->ActivityApplyService->get_info_by_id($id);
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
                $ret = $this->ActivityApplyService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改公益活动报名');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ActivityApplyService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加公益活动报名');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {
            $uids = result_to_array($data, 'uid');
            $activity_ids = result_to_array($data, 'activity_id');

            $ActivityService = \Common\Service\ActivityService::get_instance();
            $activities = $ActivityService->get_by_ids($activity_ids);
            $activities_map = result_to_map($activities, 'id');

            $VolunteerService = \Common\Service\VolunteerService::get_instance();
            $volunteers = $VolunteerService->get_by_uids($uids);
            $volunteers_map = result_to_map($volunteers,'uid');

            $status_map = \Common\Model\NfActivityApplyModel::$status_map;
            foreach ($data as $k => $v) {
                if (isset($activities_map[$v['activity_id']])) {
                    $data[$k]['activity'] = $activities_map[$v['activity_id']];
                }
                if (isset($volunteers_map[$v['uid']])) {
                    $data[$k]['volunteer'] = $volunteers_map[$v['uid']];
                }

                $data[$k]['status_desc'] = isset($status_map[$v['status']]) ? $status_map[$v['status']] : '未知状态';

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
            $ret = $this->ActivityApplyService->update_by_ids([$id], $data);
        }

        if ($ids) {
            $ret = $this->ActivityApplyService->update_by_ids($ids, $data);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('活动申请审核操作'.$status);
        $this->success('操作成功！');
    }


}