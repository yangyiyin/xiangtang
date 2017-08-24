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

        if ($this->type) {
            $where['type'] = ['EQ', $this->type];
        }

        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids) {
            $where['seller_uid'] = ['not in', $franchisee_uids];
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

    public function franchisee() {

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

        if ($this->type) {
            $where['type'] = ['EQ', $this->type];
        }

        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids) {
            $where['seller_uid'] = ['in', $franchisee_uids];
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

            $orderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
            $order_ids = result_to_array($data);

            $snapshots = $orderSnapshotService->get_by_order_ids($order_ids);
            $snapshots_map = result_to_map($snapshots, 'order_id');

            $userCourierService = \Common\Service\UserCourierService::get_instance();
            $uids = result_to_array($data, 'uid');
            $user_courier = $userCourierService->get_by_uids($uids);
            $user_courier_map = result_to_map($user_courier, 'uid');

            $OrderExpressService = \Common\Service\OrderExpressService::get_instance();
            $expresses = $OrderExpressService->get_by_oids($order_ids);
            $expresses_map = result_to_map($expresses, 'oid');
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
                $data[$key]['express'] = isset($expresses_map[$_item['id']]) ? $expresses_map[$_item['id']] : [];

            }
            //var_dump($data);die();
        }
    }

    public function order_step() {
        $step = I('get.step');
        $order_id = I('id');
        $order_ids = I('ids');
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

        //发货信息
        $OrderExpressService = \Common\Service\OrderExpressService::get_instance();
        if ($step == 'send') {
            if ($order_id) {
                $order = $ret->data;
                $data = [];
                $data['oid'] = $order_id;
                $data['express_no'] = I('post.express_no');
                $data['express_entity'] = I('post.express_name');
                $data['order_no'] = $order['order_no'];
                $data['express_type'] = $order['receiving_type'] ? $order['receiving_type'] : 1;
                $OrderExpressService->add_one($data);
            } elseif ($order_ids) {
                $orders = $ret->data;
                $data = [];
                foreach ($orders as $order) {
                    $data_temp = [];
                    $data_temp['oid'] = $order['id'];
                    $data_temp['express_no'] = I('post.express_no');
                    $data_temp['express_entity'] = I('post.express_name');
                    $data_temp['order_no'] = $order['order_no'];
                    $data_temp['express_type'] = $order['receiving_type'] ? $order['receiving_type'] : 1;
                    $data[] = $data_temp;
                }
                $OrderExpressService->add_batch($data);
            }
        }

        action_user_log('修改订单状态');
        $this->success('订单状态更新成功~');
    }


    public function set_freight() {
        $ConfService = \Common\Service\ConfService::get_instance();
        if (IS_POST) {
            $sum = I('post.sum');
            $freight = I('post.freight');
            $content = json_encode(['sum'=>intval($sum), 'freight'=>intval($freight)]);
            $ret = $ConfService->update_by_key_name('order_freight', ['content' => $content]);

            if (!$ret->success) {
                $this->error($ret->message);
            }
            action_user_log('修改运费设置');
            $this->success('订修改成功~');

        }
        $info = $ConfService->get_by_key_name('order_freight');
        if ($info) {
            $info['content'] = json_decode($info['content'], TRUE);
        }
        $this->assign('info', $info);
        $this->display();
    }


}