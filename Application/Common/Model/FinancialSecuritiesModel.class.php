<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 16:31:36
 */
namespace Common\Model;
use Think\Model;
class FinancialSecuritiesModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Volume', 'currency', '请检成交额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('New_Account', 'number', '请检查新开户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Assets', 'currency', '请检查产总值格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit', 'currency', '请检查利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('Volume_yoy','set_Volume_yoy',self::MODEL_BOTH,'callback'),
        array('G_Volume','set_G_Volume',self::MODEL_BOTH,'callback'),
        array('G_Volume_yoy','set_G_Volume_yoy',self::MODEL_BOTH,'callback'),
        array('New_Account_yoy','set_New_Account_yoy',self::MODEL_BOTH,'callback'),
        array('G_New_Account','set_G_New_Account',self::MODEL_BOTH,'callback'),
        array('G_New_Account_yoy','set_G_New_Account_yoy',self::MODEL_BOTH,'callback'),
        array('Assets_yoy','set_Assets_yoy',self::MODEL_BOTH,'callback'),
        array('Profit_yoy','set_Profit_yoy',self::MODEL_BOTH,'callback'),
    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function set_Volume_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['Volume']) && $last_year_data['Volume']) {
            $yoy = fix_2(($data['Volume'] - $last_year_data['Volume']) / $last_year_data['Volume']) ;
        }
        return $yoy;
    }


    protected function set_G_Volume($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Volume'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Volume'];
        }
        return $G_value;
    }

    protected function set_G_Volume_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['G_Volume']) && $last_year_data['G_Volume']) {
            $yoy = fix_2(($data['G_Volume'] - $last_year_data['G_Volume']) / $last_year_data['G_Volume']) ;
        }
        return $yoy;
    }

    protected function set_New_Account_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['New_Account']) && $last_year_data['New_Account']) {
            $yoy = fix_2(($data['New_Account'] - $last_year_data['New_Account']) / $last_year_data['New_Account']) ;
        }
        return $yoy;
    }


    protected function set_G_New_Account($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['New_Account'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['New_Account'];
        }
        return $G_value;
    }

    protected function set_G_New_Account_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['G_New_Account']) && $last_year_data['G_New_Account']) {
            $yoy = fix_2(($data['G_New_Account'] - $last_year_data['G_New_Account']) / $last_year_data['G_New_Account']) ;
        }
        return $yoy;
    }


    protected function set_Assets_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['Assets']) && $last_year_data['Assets']) {
            $yoy = fix_2(($data['Assets'] - $last_year_data['Assets']) / $last_year_data['Assets']) ;
        }
        return $yoy;
    }

    protected function set_Profit_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\SecuritiesService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['Profit']) && $last_year_data['Profit']) {
            $yoy = fix_2(($data['Profit'] - $last_year_data['Profit']) / $last_year_data['Profit']) ;
        }
        return $yoy;
    }

}