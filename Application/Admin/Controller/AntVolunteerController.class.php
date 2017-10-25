<?php
/**
 * Created by newModule.
 * Time: 2017-10-25 16:04:06
 */
namespace Admin\Controller;

class AntVolunteerController extends AdminController {
    protected $VolunteerService;
    protected function _initialize() {
        parent::_initialize();
        $this->VolunteerService = \Common\Service\VolunteerService::get_instance();
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
        list($data, $count) = $this->VolunteerService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\VolunteerService::$page_size);
        if($total>\Common\Service\VolunteerService::$page_size){
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
            $ret = $this->VolunteerService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除志愿者申请');
        $this->success('删除成功！');
    }

    public function add() {
        if ($id = I('get.id')) {
            $info = $this->VolunteerService->get_info_by_id($id);
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
                $ret = $this->VolunteerService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改志愿者申请');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->VolunteerService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加志愿者申请');
                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }
    public function convert_data(&$data) {
        if ($data) {
            $map = \Common\Model\NfVolunteerModel::$status_map;
            foreach ($data as $k => $v) {
                $data[$k]['status_desc'] = isset($map[$v['status']]) ? $map[$v['status']] : '未知';
            }
        }

    }

    public function change_status() {
        $status = I('status');
        $id = I('id');
        if ($status == 'approve') {
            $data = [];
            $data['status'] = \Common\Model\NfVolunteerModel::STATUS_OK;
            $ret = $this->VolunteerService->update_by_id($id,$data);
            if (!$ret->success) {
                $this->error('操作失败!');
            }
            $this->success('操作成功!');
        } elseif ($status == 'reject') {
            $data['status'] = \Common\Model\NfVolunteerModel::STATUS_BACK;
            $ret = $this->VolunteerService->update_by_id($id,$data);
            if (!$ret->success) {
                $this->error('操作失败!');
            }
            $this->success('操作成功!');
        }
        $this->error('非法操作!');
    }
}