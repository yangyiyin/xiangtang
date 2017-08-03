<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 07:52:07
 */
namespace Common\Model;
use Think\Model;
class FinancialFuturesModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('C_Volume', 'currency', '请检成交量格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Turnover', 'currency', '请检查成交额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Account', 'number', '请检查新开户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Assets', 'currency', '请检查资产总值格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Profit', 'currency', '请检查利润总值格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('G_Volume','set_G_Volume',self::MODEL_BOTH,'callback'),
        array('G_Volume_yoy','set_G_Volume_yoy',self::MODEL_BOTH,'callback'),
        array('G_Turnover','set_G_Turnover',self::MODEL_BOTH,'callback'),
        array('G_Turnover_yoy','set_G_Turnover_yoy',self::MODEL_BOTH,'callback'),
        array('G_Account','set_G_Account',self::MODEL_BOTH,'callback'),
        array('G_Account_yoy','set_G_Account_yoy',self::MODEL_BOTH,'callback'),
        array('C_Assets_yoy','set_C_Assets_yoy',self::MODEL_BOTH,'callback'),
        array('C_Profit_yoy','set_C_Profit_yoy',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function set_G_Volume_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\FuturesService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        if (isset($last_year_data['G_Volume']) && $last_year_data['G_Volume']) {
            return ($data['G_Volume'] / $last_year_data['G_Volume'] - 1) * 100;
        }
        return 0;
    }

    protected function set_G_Turnover_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\FuturesService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        if (isset($last_year_data['G_Turnover']) && $last_year_data['G_Turnover']) {
            return ($data['G_Turnover'] / $last_year_data['G_Turnover'] - 1) * 100;
        }
        return 0;
    }

    protected function set_G_Account_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\FuturesService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        if (isset($last_year_data['G_Account']) && $last_year_data['G_Account']) {
            return ($data['G_Account'] / $last_year_data['G_Account'] - 1) * 100;
        }
        return 0;
    }


    protected function set_C_Assets_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\FuturesService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        if (isset($last_year_data['C_Assets']) && $last_year_data['C_Assets']) {
            return ($data['C_Assets'] / $last_year_data['C_Assets'] - 1) * 100;
        }
        return 0;
    }

    protected function set_C_Profit_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\FuturesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        if (isset($last_year_data['C_Profit']) && $last_year_data['C_Profit']) {
            return ($data['C_Profit'] / $last_year_data['C_Profit'] - 1) * 100;
        }
        return 0;
    }


    protected function set_G_Volume($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\FuturesService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['C_Volume'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['C_Volume'];
        }
        return $G_value;
    }

    protected function set_G_Turnover($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\FuturesService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['C_Turnover'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['C_Turnover'];
        }
        return $G_value;
    }

    protected function set_G_Account($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\FuturesService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['C_Account'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['C_Account'];
        }
        return $G_value;
    }



}