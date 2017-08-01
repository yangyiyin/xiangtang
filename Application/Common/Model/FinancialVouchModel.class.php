<?php
/**
 * Created by newModule.
 * Time: 2017-07-31 14:53:05
 */
namespace Common\Model;
use Think\Model;
class FinancialVouchModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


        array('C_Balance', 'currency', '请检查本期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Quantity', 'number', '请检本期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('C_Balance_Ly', 'currency', '请检查去年同期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('C_Quantity_Ly', 'number', '请检查去年同期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('G_Balance', 'currency', '请检查本年余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('G_Quantity', 'number', '请检查本年笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Balance_Ly', 'currency', '请检查去年同期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Quantity_Ly', 'number', '请检查去年同期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Income', 'currency', '请检查本月收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Income', 'currency', '请检查本年收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Recover', 'currency', '请检查本月收回格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Recover', 'currency', '请检查本年收回格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Profit', 'currency', '请检查本期利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Profit', 'currency', '请检查本年利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Taxable', 'currency', '请检查本期纳税总额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Taxable', 'currency', '请检查本年纳税总额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Vouch', 'currency', '请检查本期担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Vouch', 'currency', '请检查本年担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Vouch_Ly', 'currency', '请检查本年同期担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('C_Balance_Ly','set_C_Balance_Ly',self::MODEL_BOTH,'callback'),
        array('C_Quantity_Ly','set_C_Quantity_Ly',self::MODEL_BOTH,'callback'),
        array('G_Quantity_Ly','set_G_Quantity_Ly',self::MODEL_BOTH,'callback'),
        array('G_Income','set_G_Income',self::MODEL_BOTH,'callback'),
        array('G_Recover','set_G_Recover',self::MODEL_BOTH,'callback'),
        array('G_Vouch','set_G_Vouch',self::MODEL_BOTH,'callback'),
        array('G_Vouch_Ly','set_G_Vouch_Ly',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function set_C_Balance_Ly($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        return $last_year_data ? $last_year_data['C_Balance'] : 0;
    }

    protected function set_C_Quantity_Ly($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        return $last_year_data ? $last_year_data['C_Quantity'] : 0;
    }

    protected function set_G_Quantity_Ly($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        return $last_year_data ? $last_year_data['G_Quantity'] : 0;
    }


    protected function set_G_Income($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $ret = $InsurancePropertyService->get_this_year_data($year, $month, $data['all_name']);
        $G_Income = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_Income += $value['C_Income'];
            }
        }
        if (!$has_this_data) {
            $G_Income += $data['C_Income'];
        }
        return $G_Income;
    }

    protected function set_G_Recover($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $ret = $InsurancePropertyService->get_this_year_data($year, $month, $data['all_name']);
        $G_Recover = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_Recover += $value['C_Recover'];
            }
        }
        if (!$has_this_data) {
            $G_Recover += $data['C_Recover'];
        }
        return $G_Recover;
    }

    protected function set_G_Vouch($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $ret = $InsurancePropertyService->get_this_year_data($year, $month, $data['all_name']);
        $G_Vouch = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_Vouch += $value['C_Vouch'];
            }
        }
        if (!$has_this_data) {
            $G_Vouch += $data['C_Vouch'];
        }
        return $G_Vouch;
    }

    protected function set_G_Vouch_Ly($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\VouchService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        return $last_year_data ? $last_year_data['G_Vouch'] : 0;
    }
}