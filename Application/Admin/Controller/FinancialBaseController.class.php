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

    public function submit_monthly($data = []) {
        $this->assign('title', $this->title);
        //获取当期的数据
        $info = [];
        if (isset($data['all_name']) && $data['all_name']) {
            $info = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $data['all_name']);
        }
        $this->assign('info', $info);
    }

    public function statistics() {
        $this->assign('title', $this->title);
    }
    public function add_unit() {
        $this->assign('title', $this->title);

    }
    public function check_by_month_year($year, $month, $all_name) {
        if (!$year || !$month || !$all_name) {
            return false;
        }
        $ret = $this->local_service->get_by_month_year(intval(date('Y')), intval(date('m')), $all_name);
        if (!$ret) {
            return false;
        }
        return true;
    }

}