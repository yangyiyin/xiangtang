<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntOrderController extends AdminController {
    protected $OrderService;
    protected function _initialize() {
        parent::_initialize();
        $this->OrderService = \Common\Service\OrderService::get_instance();
    }

    public function index() {

        $where = [];


        if (I('get.order_no')) {
            $where['order_no'] = ['EQ', I('get.order_no')];
        }
        if (I('get.status')) {
            $where['status'] = ['EQ', I('get.status')];
        }

        if (I('get.create_begin')) {
            $where['create_time'][] = ['EGT', I('get.create_begin')];
        }
        if (I('get.create_end')) {
            $where['create_time'][] = ['ELT', I('get.create_end')];
        }
        $UserService = \Common\Service\UserService::get_instance();
        if ($user_tel = I('get.user_tel')) {
            //$UserService = \Common\Service\UserService::get_instance();
            $info = $UserService->get_by_tel($user_tel);
            if ($info) {
                $where['uid'][] = ['eq', $info['id']];
            } else {
                $this->error('没有该用户');
            }
        }

        if ($entity_name = I('get.entity_name')) {
            // $UserService = \Common\Service\UserService::get_instance();
            $user_where = [];
            $user_where['entity_name'] =  ['eq', $entity_name];
            $users = $UserService->get_by_where_all($user_where);
            if ($users) {
                $uids = result_to_array($users);
                $where['uid'][] = ['in', $uids];
            } else {
                $this->error('没有该企业名');
            }
        }

        if ($service_name = I('get.service_name')) {
            $ServicesService = \Common\Service\ServicesService::get_instance();
            $service_where = [];
            $service_where['title'] = ['eq', $service_name];
            list($services,) = $ServicesService->get_by_where_all($service_where);
            if ($services) {
                $service_ids = result_to_array($services);
                $user_where = [];
                $user_where[] = ['service_id' => ['in', $service_ids]];
                $users = $UserService->get_by_where_all($user_where);
                if ($users) {
                    $uids = result_to_array($users);
                    $where['uid'][] = ['in', $uids];
                } else {
                    $this->error('该网点没有用户');
                }
            } else {
                $this->error('没有该网点');
            }
        }

        if ($this->type) {
            $where['type'] = ['EQ', $this->type];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->OrderService->get_by_where($where, 'id desc', $page);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\OrderService::$page_size);
        if($total>\Common\Service\OrderService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display('AntOrder/index');
    }

    private function convert_data(&$data) {
        if ($data) {
            $uids = result_to_array($data, 'uid');

            $orderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
            $order_ids = result_to_array($data);

            $snapshots = $orderSnapshotService->get_by_order_ids($order_ids);
            $snapshots_map = result_to_map($snapshots, 'order_id');

            $userCourierService = \Common\Service\UserCourierService::get_instance();
            $user_courier = $userCourierService->get_by_uids($uids);
            $user_courier_map = result_to_map($user_courier, 'uid');

            $userService = \Common\Service\UserService::get_instance();
            $users = $userService->get_by_uids($uids);
            $users_map =  result_to_map($users, 'id');
            foreach ($data as $key => $_item) {
                if (isset($snapshots_map[$_item['id']])) {
                    $data[$key]['order_snapshot'] = json_decode($snapshots_map[$_item['id']]['content'], TRUE);
                    $order_stock_out_info = '';
                    foreach ($data[$key]['order_snapshot'] as $_order_item) {
                        $order_stock_out_info .= $_order_item['title'] .'出库'. $_order_item['num'] . $_order_item['unit_desc'] . ',';
                    }
                    $data[$key]['order_stock_out_info'] = substr($order_stock_out_info, 0, -1);
                }

                //var_dump($data[$key]['order_snapshot']);die();
                $data[$key]['status_desc'] = $this->OrderService->get_status_txt($_item['status']);
                $data[$key]['type_desc'] = $this->OrderService->get_type_txt($_item['type']);
                $data[$key]['courier'] = isset($user_courier_map[$_item['uid']]) ? $user_courier_map[$_item['uid']] : [];
                $data[$key]['user_info'] = isset($users_map[$_item['uid']]) ? $users_map[$_item['uid']] : [];

            }
            //var_dump($data);die();
        }
    }

    public function order_step() {
        $step = I('get.step');
        $order_id = I('get.id');
        $order_ids = I('post.ids');
        if ($order_id) {
            $ret = $this->OrderService->process($order_id, $step);
        } elseif ($order_ids) {
            $ret = $this->OrderService->batch_process($order_ids, $step);
        } else {
            $this->error('没有传订单id~');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('修改订单状态');
        $this->success('订单状态更新成功~');
    }

    public function order_info() {
        $order_id = I('get.id');
        $info = $this->OrderService->get_info_by_id($order_id);

        if (!$info) {
            $this->error('没有该订单');
        } else {
            $list = [$info];
            $this->convert_data($list);
            $this->assign('vo', $list[0]);
        }

        $this->display('AntOrder/info');
    }


}