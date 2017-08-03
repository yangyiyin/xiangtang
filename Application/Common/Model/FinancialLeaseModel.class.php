<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 09:09:56
 */
namespace Common\Model;
use Think\Model;
class FinancialLeaseModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Capital', 'currency', '请检成实缴注册资本格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Owner', 'currency', '请检查所有者权益格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Assets_M', 'currency', '请检查月末余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Business_Stay', 'number', '请检查月末留存笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Business_M_New', 'number', '请检查当月新增笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Client_Stay', 'number', '请检成月末留存户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Client_M_New', 'number', '请检当月新增户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Business_C1', 'currency', '请检售后回租占比格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Business_C2', 'currency', '请检平均收益率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Business_C3', 'currency', '请检租金回收率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit_A', 'currency', '请检当月营业收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit_B', 'currency', '请检当月累计净利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit_C', 'currency', '请检当月应缴增值税格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit_D', 'currency', '请检当月应缴所得税格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('Assets_Y','set_Assets_Y',self::MODEL_BOTH,'callback'),
        array('Business_Y_New','set_Business_Y_New',self::MODEL_BOTH,'callback'),
        array('Business_T_New','set_Business_T_New',self::MODEL_BOTH,'callback'),
        array('Client_Y_New','set_Client_Y_New',self::MODEL_BOTH,'callback'),
        array('Client_T_New','set_Client_T_New',self::MODEL_BOTH,'callback'),
        array('Profit_AY','set_Profit_AY',self::MODEL_BOTH,'callback'),
        array('Profit_BY','set_Profit_BY',self::MODEL_BOTH,'callback'),
        array('Profit_CY','set_Profit_CY',self::MODEL_BOTH,'callback'),
        array('Profit_DY','set_Profit_DY',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }



    protected function set_Assets_Y($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Assets_M'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Assets_M'];
        }
        return $G_value;
    }

    protected function set_Profit_AY($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Profit_A'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Profit_A'];
        }
        return $G_value;
    }


    protected function set_Profit_BY($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Profit_B'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Profit_B'];
        }
        return $G_value;
    }

    protected function set_Profit_CY($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Profit_C'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Profit_C'];
        }
        return $G_value;
    }

    protected function set_Profit_DY($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Profit_D'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Profit_D'];
        }
        return $G_value;
    }

    protected function set_Business_Y_New($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Business_M_New'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Business_M_New'];
        }
        return $G_value;
    }

    protected function set_Business_T_New($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Business_M_New'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Business_M_New'];
        }
        return $G_value;
    }

    protected function set_Client_Y_New($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Client_M_New'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Client_M_New'];
        }
        return $G_value;
    }

    protected function set_Client_T_New($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LeaseService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Client_M_New'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Client_M_New'];
        }
        return $G_value;
    }

}