<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class FinancialBaseController extends AdminController {
    protected $title = '';
    protected $local_service;
    protected $local_service_name;
    protected $is_history = false;
    protected function _initialize() {
        parent::_initialize();
        //FinancialInsuranceMutual
        $service_name = str_replace('Financial', '', CONTROLLER_NAME) . 'Service';
        $service = '\Common\Service\\'.$service_name;
        $this->local_service_name = $service_name;
        $this->local_service = $service::get_instance();
    }

    public function submit_monthly() {
        $this->assign('title', $this->title);

        //获取所有相关的公司
        $DepartmentService = \Common\Service\DepartmentService::get_instance();

        $departments = $DepartmentService->get_my_list(UID, $this->type);


        if (!$departments) {
            $departments = $DepartmentService->get_all_list($this->type);
        } else {
            $data = $departments[0];
        }
        $departments = result_to_array($departments, 'all_name');
        $this->assign('departments', $departments);

        //获取当期的数据
        $info = [];
        if (!$this->is_history) {
            if (isset($data['all_name']) && $data['all_name']) {
                $info = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);
                $this->convert_data_submit_monthly($info);
            }
        }
        $this->assign('info', $info);
    }

    protected function convert_data_submit_monthly(&$info) {

    }
    public function statistics() {
        $this->assign('title', $this->title);
        $get = I('get.');
        $where = [];
        if ($get['all_name']) {
            $where['all_name'] = ['LIKE', '%' . $get['all_name'] . '%'];
        }

        if (!$get['year']) {
            $get['year'] = intval(date('Y'));
        }
        if (!$get['month']) {
            $get['month'] = intval(date('m'));
        }
        $where['year'] = $get['year'];
        $where['month'] = $get['month'];
        $service = '\Common\Service\\'.$this->local_service_name;
        $page = I('get.p', 1);
        $where_all = [];
        $where_all['year'] = $get['year'];
        $where_all['month'] = $get['month'];
        $data_all = $this->local_service->get_by_where_all($where_all);
        list($data, $count) = $this->local_service->get_by_where($where, 'income desc', $page);
        $data = $this->convert_data_statistics($data, $data_all);
        $PageInstance = new \Think\Page($count, $service::$page_size);
        if($total>$service::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);
    }

    public function add_unit() {
        $this->assign('title', $this->title);

    }
    public function check_by_month_year($year, $month, $all_name) {
        if (!$year || !$month || !$all_name) {
            return false;
        }
        $ret = $this->local_service->get_by_month_year($year, $month, $all_name);

        if ($ret) {
            return $ret;
        }
        return true;
    }

    public function add_history() {
        $this->is_history = true;
        $this->submit_monthly();
    }

    public function submit_log() {

        $where = [];
        if (I('get.all_name')) {
            $where['all_name'] = ['LIKE', '%' . I('get.all_name') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->local_service->get_by_where($where, 'id desc', $page);
        $this->convert_data_submit_log($data);
        $service = '\Common\Service\\'.$this->local_service_name;
        $PageInstance = new \Think\Page($count, $service::$page_size);
        if($total>$service::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }

    protected function convert_data_submit_log($data) {
        //子类实现

    }
    protected function convert_data_statistics($data, $data_all) {
        //子类实现
        return $data;
    }
}