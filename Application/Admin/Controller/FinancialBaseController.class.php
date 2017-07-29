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
    protected function _initialize() {
        parent::_initialize();
        //FinancialInsuranceMutual
        $service_name = str_replace('Financial', '', CONTROLLER_NAME) . 'Service';
        $service = '\Common\Service\\'.$service_name;
        $this->local_service_name = $service_name;
        $this->local_service = $service::get_instance();
    }

    public function submit_monthly() {
        //$current = $this->get_current_monthly();
        $this->assign('title', $this->title);
        //获取当期的数据
        $info = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')));
       // var_dump($info);die();
        $this->assign('info', $info);
    }

    public function statistics() {
        $this->assign('title', $this->title);
    }
    public function add_unit() {
        $this->assign('title', $this->title);

    }
    public function check_by_month_year($year, $month) {
        if (!$year || !$month) {
            return false;
        }
        $ret = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')));
        if (!$ret) {
            return false;
        }
        return true;
    }

}