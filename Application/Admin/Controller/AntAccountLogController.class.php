<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 15:56:48
 */
namespace Admin\Controller;

class AntAccountLogController extends AdminController {
    protected $AccountLogService;
    protected function _initialize() {
        parent::_initialize();
        $this->AccountLogService = \Common\Service\AccountLogService::get_instance();
    }

    public function platform() {

        $where = [];
        if (I('get.order_no')) {
            $where['order_no'] = ['LIKE', '%' . I('get.order_no') . '%'];
        }

        if (I('get.entity_title')) {
            $where_user = [];
            $where_user['entity_title'] = ['LIKE', '%' . I('get.entity_title') . '%'];
            $UserService = \Common\Service\UserService::get_instance();
            list($users,$count) = $UserService->get_by_where($where_user);
            if ($users) {
                $where['uid'] = ['in', result_to_array($users)];
            } else {
                $where['uid'] = ['in', [0]];
            }
        }

        $where['status'] = ['in', [\Common\Model\NfOrderModel::STATUS_PAY,\Common\Model\NfOrderModel::STATUS_SENDING, \Common\Model\NfOrderModel::STATUS_DONE]];

        $create_begin = I('get.create_begin');
        $create_end = I('get.create_end');

        if (!$create_begin) {
            $create_begin = date('Y-m-d', time()-7*24*3600);
        }

        if (!$create_end) {
            $create_end = date('Y-m-d', time()+7*24*3600);
        }

        $where[] = ['create_time' => ['egt', $create_begin]];
        $where[] = ['create_time' => ['elt', $create_end]];

        $this->assign('create_begin', $create_begin);
        $this->assign('create_end', $create_end);
        //获取加盟商的uids
        $MemberService = \Common\Service\MemberService::get_instance();
        $franchisee_uids = $MemberService->get_franchisee_uids();
        if ($franchisee_uids) {
            $where['seller_uid'] = ['not in', $franchisee_uids];
        }

        if (I('get.pay_type')) {
            $where['pay_type'] = ['eq', I('get.pay_type')];
        }
        $page = I('get.p', 1);
        $OrderService = \Common\Service\OrderService::get_instance();
        if (I('export')) {
            list($data, $count) = $OrderService->get_by_where_all($where);
        } else {
            list($data, $count) = $OrderService->get_by_where($where, 'id desc', $page);
        }

        $this->convert_data_order($data);
        $PageInstance = new \Think\Page($count, \Common\Service\OrderService::$page_size);
        if($total>\Common\Service\OrderService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

//        $this->assign('total_sum', $sum);
//        $this->assign('dealer_profit_sum', $dealer_profit_sum);

        if (I('export')) {


            $excel_data = [];
            $excel_data[] = ["订单号","订单单位","订单金额","支付方式","佣金","订单时间"];
            foreach ($data as $value) {
                $temp = [];
                $temp[] = $value['order_no'];
                $temp[] = isset($value['user']) ? $value['user']['entity_title'] : '';
                $temp[] = format_price($value['sum']) . '元';
                $temp[] = $value['pay_type_desc'];
                $temp[] = format_price($value['dealer_profit']) . '元';
                $temp[] = $value['create_time'];
                $excel_data[] = $temp;
            }


            exportexcel($excel_data,'平台账务', '平台订单账务');
            exit();
        }

        $this->display();
    }


    public function franchisee() {

        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_FRANCHISEE_ADD, \Common\Model\NfAccountLogModel::TYPE_FRANCHISEE_MINUS]];

        $create_begin = I('get.create_begin');
        $create_end = I('get.create_end');

        if (!$create_begin) {
            $create_begin = date('Y-m-d', time()-7*24*3600);
        }

        if (!$create_end) {
            $create_end = date('Y-m-d', time()+7*24*3600);
        }

        $where[] = ['create_time' => ['egt', $create_begin]];
        $where[] = ['create_time' => ['elt', $create_end]];
        if (I('get.uid')) {
            $where[] = ['uid' => ['eq', I('get.uid')]];
        }
        $this->assign('create_begin', $create_begin);
        $this->assign('create_end', $create_end);
        $page = I('get.p', 1);
        list($data, $count) = $this->AccountLogService->get_by_where($where, 'id desc', $page);
        list($sum,$total_pay_num) = $this->AccountLogService->get_totals($where);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\AccountLogService::$page_size);
        if($total>\Common\Service\AccountLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('total_sum', $sum);
        $this->assign('total_pay_num', $total_pay_num);
        $this->assign('total_order_num', $total_pay_num);
        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    public function inviter() {

        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS]];

        $create_begin = I('get.create_begin');
        $create_end = I('get.create_end');

        if (!$create_begin) {
            $create_begin = date('Y-m-d', time()-7*24*3600);
        }

        if (!$create_end) {
            $create_end = date('Y-m-d', time()+7*24*3600);
        }

        $where[] = ['create_time' => ['egt', $create_begin]];
        $where[] = ['create_time' => ['elt', $create_end]];
        if (I('get.uid')) {
            $where[] = ['uid' => ['eq', I('get.uid')]];
        }
        $this->assign('create_begin', $create_begin);
        $this->assign('create_end', $create_end);
        $page = I('get.p', 1);
        list($data, $count) = $this->AccountLogService->get_by_where($where, 'id desc', $page);
        list($sum,$total_pay_num) = $this->AccountLogService->get_totals($where);
        $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\AccountLogService::$page_size);
        if($total>\Common\Service\AccountLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('total_sum', $sum);
        $this->assign('total_pay_num', $total_pay_num);
        $this->assign('total_order_num', $total_pay_num);
        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    /**
     * 所有佣金记录
     */
    public function all_commission() {

        $where = $where_arr = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD,\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS,\Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS,\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS, \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];
        $where_arr[] = 'type in ('.join(',',[\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD,\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS,\Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS,\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS, \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]).') ';
        $create_begin = I('get.create_begin');
        $create_end = I('get.create_end');

        if ($create_begin) {
            $where[] = ['create_time' => ['egt', $create_begin]];
            $where_arr[] = 'create_time >= "'.$create_begin .'"';
        }
        if ($create_end) {
            $where[] = ['create_time' => ['elt', $create_end]];
            $where_arr[] = 'create_time <= "'.$create_end.'"';
        }
        if (I('get.uid')) {
            $where[] = ['uid' => ['eq', I('get.uid')]];
            $where_arr[] = 'uid = '.I('get.uid');
        }

        if (I('get.entity_title')) {
            $where_user = [];
            $where_user['entity_title'] = ['LIKE', '%' . I('get.entity_title') . '%'];
            $UserService = \Common\Service\UserService::get_instance();
            list($users,$count) = $UserService->get_by_where($where_user);
            if ($users) {
                $where['uid'] = ['in', result_to_array($users)];
                $where_arr[] = 'uid in ('.join(',',result_to_array($users)).') ';
            } else {
                $where['uid'] = ['in', [0]];
                $where_arr[] = 'uid in (0) ';
            }
        }


        $this->assign('create_begin', $create_begin);
        $this->assign('create_end', $create_end);
        $page = I('get.p', 1);
        //list($data, $count) = $this->AccountLogService->get_by_where($where, 'id desc', $page);
        if (I('export')) {
            list($data, $count) = $this->AccountLogService->get_group_by_uid_all(join(' and ', $where_arr));

        } else {
            list($data, $count) = $this->AccountLogService->get_group_by_uid(join(' and ', $where_arr), 'id desc',$page);

        }

        $data = $this->convert_commission_data($data, $where);
        //echo json_encode($data);die();
        $PageInstance = new \Think\Page($count, \Common\Service\AccountLogService::$page_size);
        if($total>\Common\Service\AccountLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        if (I('export')) {
            $excel_data = [];
            $excel_data[] = ["服务站名称","初始金额","官方充值","官方扣除","经销商佣金收入","交易支出","提现","结余金额"];
            foreach ($data as $value) {
                $temp = [];
                $temp[] = $value['user']['entity_title'];
                $temp[] = format_price($value['account_before']);
                $temp[] = format_price($value['a_sum']);
                $temp[] = format_price($value['b_sum']);
                $temp[] = format_price($value['c_sum']);
                $temp[] = format_price($value['d_sum']);
                $temp[] = format_price($value['e_sum']);
                $temp[] = format_price($value['account']);
                $excel_data[] = $temp;
            }
            exportexcel($excel_data,'', '佣金明细');
            exit();
        }

        $this->display();
    }

    /**
     * 所有佣金记录
     */
    public function all_commission_old() {

        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD,\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS,\Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS,\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS, \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];

        $create_begin = I('get.create_begin');
        $create_end = I('get.create_end');

        if ($create_begin) {
            $where[] = ['create_time' => ['egt', $create_begin]];
        }
        if ($create_end) {
            $where[] = ['create_time' => ['elt', $create_end]];
        }
        if (I('get.uid')) {
            $where[] = ['uid' => ['eq', I('get.uid')]];
        }

        if (I('get.entity_title')) {
            $where_user = [];
            $where_user['entity_title'] = ['LIKE', '%' . I('get.entity_title') . '%'];
            $UserService = \Common\Service\UserService::get_instance();
            list($users,$count) = $UserService->get_by_where($where_user);
            if ($users) {
                $where['uid'] = ['in', result_to_array($users)];
            } else {
                $where['uid'] = ['in', [0]];
            }
        }


        $this->assign('create_begin', $create_begin);
        $this->assign('create_end', $create_end);
        $page = I('get.p', 1);
        //list($data, $count) = $this->AccountLogService->get_by_where($where, 'id desc', $page);

        if (I('export')) {
            list($data, $count) = $this->AccountLogService->get_by_where_all($where);
        } else {
            list($data, $count) = $this->AccountLogService->get_by_where($where, 'id desc', $page);
        }

        list($sum,$total_pay_num) = $this->AccountLogService->get_totals($where);
        $data = $this->convert_commission_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\AccountLogService::$page_size);
        if($total>\Common\Service\AccountLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        if (I('export')) {
            $excel_data = [];
            $excel_data[] = ["用户id","服务站名称","明细","备注","发生时间"];
            foreach ($data as $value) {
                $temp = [];
                $temp[] = $value['uid'];
                $temp[] = isset($value['user']) ? $value['user']['entity_title'] : '';
                $temp[] = $value['info'];
                $temp[] = $value['remark'];
                $temp[] = $value['create_time'];
                $excel_data[] = $temp;
            }
            exportexcel($excel_data,'', '佣金明细');
            exit();
        }


        $this->display();
    }

    public function convert_data(&$data) {

    }
    public function convert_commission_data($data, $where='') {

        if ($data) {
            $uids = result_to_array($data, 'uid');
            $type_map = \Common\Model\NfAccountLogModel::$TYPE_MAP;
            $UserService = \Common\Service\UserService::get_instance();
            $users = $UserService->get_by_ids($uids);
            $users_map = result_to_map($users);
            $where['uid'] = ['in', $uids];
            list($all_datas, ) = $this->AccountLogService->get_by_where_all($where);
            $all_datas_map = result_to_complex_map($all_datas, 'uid');

            $oids = result_to_array($all_datas, 'oid');
            if ($oids) {
                $OrderService = \Common\Service\OrderService::get_instance();
                $orders = $OrderService->get_by_ids($oids);
                $orders_map = result_to_map($orders);
            }

            $AccountService = \Common\Service\AccountService::get_instance();
            $accounts = $AccountService->get_by_uids($uids);
            $accounts_map = result_to_map($accounts, 'uid');

            //获取截止结束时间的佣金综合
            $unset_key = '';
            foreach ($where as $k => $v) {
                if (isset($v['create_time']['egt'])) {
                    $unset_key = $k;
                    break;
                }
            }
            if ($unset_key) {
                unset($where[$unset_key]);
            }
            list($all_datas_sum, ) = $this->AccountLogService->get_by_where_all($where);
            $all_datas_sum_map = result_to_complex_map($all_datas_sum, 'uid');
            $uid_sum_map = [];
            foreach ($all_datas_sum_map as $uid => $list) {
                $uid_sum_map[$uid] = array_sum(result_to_array($list, 'sum'));
            }

            foreach ($data as $key => $value) {
//                $data[$key]['type_desc'] = isset($type_map[$value['type']]) ? $type_map[$value['type']] : '未知类型';
//                $data[$key]['info'] = $data[$key]['type_desc'] . format_price($value['sum']) . '元';

                $data[$key]['user'] = isset($users_map[$value['uid']]) ? $users_map[$value['uid']] : [];
                $data[$key]['account'] = isset($uid_sum_map[$value['uid']]) ? $uid_sum_map[$value['uid']] : 0;
                if (isset($all_datas_map[$value['uid']])) {
                    foreach ($all_datas_map[$value['uid']] as $_k => $_log) {
                        $all_datas_map[$value['uid']][$_k]['type_desc'] = isset($type_map[$_log['type']]) ? $type_map[$_log['type']] : '未知类型';
                        $all_datas_map[$value['uid']][$_k]['order'] = isset($orders_map[$_log['oid']]) ? $orders_map[$_log['oid']] : [];

                    }

                    $account_modify = array_sum(result_to_array($all_datas_map[$value['uid']], 'sum'));
                    $data_type_map = result_to_complex_map($all_datas_map[$value['uid']], 'type');
                    $data[$key]['a'] = isset($data_type_map[\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD]) ? $data_type_map[\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD] : [];
                    $data[$key]['b'] = isset($data_type_map[\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS]) ? $data_type_map[\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS] : [];
                    $data[$key]['c'] = isset($data_type_map[\Common\Model\NfAccountLogModel::TYPE_DEALER_ADD]) ? $data_type_map[\Common\Model\NfAccountLogModel::TYPE_DEALER_ADD] : [];
                    $data[$key]['d'] = isset($data_type_map[\Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS]) ? $data_type_map[\Common\Model\NfAccountLogModel::TYPE_TRADE_MINUS] : [];
                    $data[$key]['e'] = isset($data_type_map[\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS]) ? $data_type_map[\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS] : [];


                } else {
                    $account_modify = 0;
                    $data[$key]['a'] = $data[$key]['b'] = $data[$key]['c'] = $data[$key]['d'] = $data[$key]['e'] = [];

                }
                $data[$key]['a_sum'] = array_sum(result_to_array($data[$key]['a'], 'sum'));
                $data[$key]['b_sum'] = array_sum(result_to_array($data[$key]['b'], 'sum'));
                $data[$key]['c_sum'] = array_sum(result_to_array($data[$key]['c'], 'sum'));
                $data[$key]['d_sum'] = array_sum(result_to_array($data[$key]['d'], 'sum'));
                $data[$key]['e_sum'] = array_sum(result_to_array($data[$key]['e'], 'sum'));
                $data[$key]['account_before'] = $data[$key]['account'] - $account_modify;//初始金额
                if (isset($all_datas_map[$value['uid']])) {

                    foreach ($all_datas_map[$value['uid']] as $_k => $_log) {
                        $all_datas_map[$value['uid']][$_k]['type_desc'] = isset($type_map[$_log['type']]) ? $type_map[$_log['type']] : '未知类型';
                        if ($_k == 0) {
                            $all_datas_map[$value['uid']][$_k]['account'] = $data[$key]['account'];

                        } else {
                            $all_datas_map[$value['uid']][$_k]['account'] = $all_datas_map[$value['uid']][$_k-1]['account'] - $all_datas_map[$value['uid']][$_k-1]['sum'];

                        }
                        $all_datas_map[$value['uid']][$_k]['order'] = isset($orders_map[$_log['oid']]) ? $orders_map[$_log['oid']] : [];


                    }
                    $data[$key]['detail_all'] = $all_datas_map[$value['uid']];
                }
            }
        }

        return $data;

    }


    private function convert_data_order(&$data) {
        if ($data) {

            $orderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
            $order_ids = result_to_array($data);

            $snapshots = $orderSnapshotService->get_by_order_ids($order_ids);
            $snapshots_map = result_to_map($snapshots, 'order_id');

            $userCourierService = \Common\Service\UserCourierService::get_instance();
            $uids = result_to_array($data, 'uid');
            $user_courier = $userCourierService->get_by_uids($uids);
            $user_courier_map = result_to_map($user_courier, 'uid');

            $UserService = \Common\Service\UserService::get_instance();
            $users = $UserService->get_by_ids($uids);
            $users_map = result_to_map($users);
            $OrderExpressService = \Common\Service\OrderExpressService::get_instance();
            $expresses = $OrderExpressService->get_by_oids($order_ids);
            $expresses_map = result_to_map($expresses, 'oid');
            $pay_type_map = \Common\Model\NfOrderModel::$pay_type_map;
            $OrderService = \Common\Service\OrderService::get_instance();
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
                $data[$key]['status_desc'] = $OrderService->get_status_txt($_item['status']);
                $data[$key]['type_desc'] = $OrderService->get_type_txt($_item['type']);
                $data[$key]['courier'] = isset($user_courier_map[$_item['uid']]) ? $user_courier_map[$_item['uid']] : [];
                $data[$key]['express'] = isset($expresses_map[$_item['id']]) ? $expresses_map[$_item['id']] : [];
                $data[$key]['pay_type_desc'] = isset($pay_type_map[$_item['pay_type']]) ? $pay_type_map[$_item['pay_type']] : '未知支付方式';
                $data[$key]['user'] = isset($users_map[$_item['uid']]) ? $users_map[$_item['uid']] : [];


            }
            //var_dump($data);die();
        }
    }

}