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
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_PLATFORM_ADD,\Common\Model\NfAccountLogModel::TYPE_PLATFORM_MINUS]];

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

        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_ADD,\Common\Model\NfAccountLogModel::TYPE_OFFICIAL_MINUS,\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS, \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];

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
        $data = $this->convert_commission_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\AccountLogService::$page_size);
        if($total>\Common\Service\AccountLogService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }


    public function convert_data(&$data) {

    }
    public function convert_commission_data($data) {
        if ($data) {
            $type_map = \Common\Model\NfAccountLogModel::$TYPE_MAP;
            foreach ($data as $key => $value) {
                $data[$key]['type_desc'] = isset($type_map[$value['type']]) ? $type_map[$value['type']] : '未知类型';
                $data[$key]['info'] = $data[$key]['type_desc'] . format_price($value['sum']) . '元';
            }
        }

        return $data;

    }
}