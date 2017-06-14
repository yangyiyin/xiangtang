<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntUserController extends AdminController {
    private $UserService;
    protected function _initialize() {
        parent::_initialize();
        $this->UserService = \Common\Service\UserService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.type')) {
            $where['type'] = ['EQ', I('get.type')];
        }
        if (I('get.service_id')) {
            $where['service_id'] = ['EQ', I('get.service_id')];
        }
        if (I('get.create_begin')) {
            $where['create_time'][] = ['EGT', I('get.create_begin')];
        }
        if (I('get.create_end')) {
            $where['create_time'][] = ['ELT', I('get.create_end')];
        }

        if (I('get.id')) {
            $where['id'] = ['EQ', I('get.id')];
        }
        if (I('get.tel')) {
            $where['user_tel'] = ['EQ', I('get.tel')];
        }
        if (I('get.status')) {
            $where['status'] = ['EQ', I('get.status')];
        }

        if (I('get.user_name')) {
            $where['status'] = ['LIKE', '%' . I('get.user_name') . '%'];
        }
        //var_dump($where);die();
        $page = I('get.p', 1);
        list($data, $count) = $this->UserService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\UserService::$page_size);
        if($total>\Common\Service\UserService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();
        $ServicesService = \Common\Service\ServicesService::get_instance();
        $services_options = $ServicesService->get_all_option(I('get.service_id'));
        $this->assign('services_options', $services_options);

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    private function convert_data(&$data) {
        if ($data) {
            $ServicesService = \Common\Service\ServicesService::get_instance();
            $ids = result_to_array($data, 'service_id');
            $services = $ServicesService->get_by_ids($ids);
            $services_map = result_to_map($services, 'id');
           // $user_courier = $userCourierService->get_by_uids($uids);
          //  $user_courier_map = result_to_map($user_courier, 'uid');

            foreach ($data as $key => $_item) {
//                $data[$key]['type_desc'] = $this->UserService->get_type_txt($_item['type']);

                $data[$key]['status_desc'] = $this->UserService->get_status_txt($_item['status']);

                if (isset($services_map[$_item['service_id']])) {
                    $data[$key]['service'] = $services_map[$_item['service_id']];
                }



            }
        }
    }

    public function approve() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->UserService->approve([$id]);
        } elseif ($ids) {
            $ret = $this->UserService->approve($ids);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('审核通过用户');
        $this->success('通过成功！');
    }

    public function forbid() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->UserService->forbid([$id]);
        } elseif ($ids) {
            $ret = $this->UserService->forbid($ids);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('禁用用户');
        $this->success('禁用成功！');
    }

    public function approve_entity() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->UserService->approve_entity([$id]);
        } elseif ($ids) {
            $ret = $this->UserService->approve_entity($ids);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('认证通过用户');
        $this->success('认证成功！');
    }

    public function reject_entity() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->UserService->reject_entity([$id]);
        } elseif ($ids) {
            $ret = $this->UserService->reject_entity($ids);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('拒绝认证用户');
        $this->success('认证拒绝！');
    }


    public function search_courier() {
        $name = I('post.courier_name');
        $courierService = \Common\Service\CourierService::get_instance();
        list($couriers, $count) = $courierService->get_by_where(['name'=>['LIKE', '%'.$name.'%']]);
        if ($couriers) {
            //var_dump(result_to_array($couriers, 'name'));
            $this->ajaxReturn(result_to_array($couriers, 'name'));
        } else {
            $this->ajaxReturn('');
        }
    }

    public function set_courier() {
        $name = I('post.courier_name');
        $uid = I('post.uid');
        $userCourierService = \Common\Service\UserCourierService::get_instance();
        $ret = $userCourierService->set_name_by_uid($uid, $name);
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('设置业务员');
        $this->success('设置成功！');
    }

    public function entity_info() {
        $id = I('get.id');
        if (!$id) {
            $this->error('id没有');
        }
        $user = $this->UserService->get_info_by_id($id);
        if (!$user) {
            $this->error('没有找到信息~');
        }
        $this->assign('info', $user);
        $this->display();
    }
}