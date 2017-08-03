<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 12:47:42
 */
namespace Common\Model;
use Think\Model;
class FinancialLoanModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Funds_Owner', 'currency', '请检所有者权益格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Funds_Bank', 'currency', '请检查银行融资格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Month_Amount', 'currency', '请检查余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Month_Amount_N', 'number', '请检查余额笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Month_Small', 'currency', '请检查小额贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Month_Small_N', 'number', '请检小额贷款笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Interest_Rate', 'currency', '请检年利率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Bad_Debt', 'currency', '请检不良贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Net_Profit', 'currency', '请检净利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Revenue', 'currency', '请检总收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('Year_Amount','set_Year_Amount',self::MODEL_BOTH,'callback'),
        array('Year_Amount_N','set_Year_Amount_N',self::MODEL_BOTH,'callback'),
        array('Year_Small','set_Year_Small',self::MODEL_BOTH,'callback'),
        array('Year_Small_N','set_Year_Small_N',self::MODEL_BOTH,'callback'),
        array('Total_Amount','set_Total_Amount',self::MODEL_BOTH,'callback'),
        array('Total_Amount_N','set_Total_Amount_N',self::MODEL_BOTH,'callback'),
        array('Total_Small','set_Total_Small',self::MODEL_BOTH,'callback'),
        array('Total_Small_N','set_Total_Small_N',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }



    protected function set_Year_Amount($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Amount'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Amount'];
        }
        return $G_value;
    }

    protected function set_Year_Amount_N($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Amount_N'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Amount_N'];
        }
        return $G_value;
    }


    protected function set_Year_Small($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Small'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Small'];
        }
        return $G_value;
    }

    protected function set_Year_Small_N($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_this_year_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Small_N'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Small_N'];
        }
        return $G_value;
    }

    protected function set_Total_Amount($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Amount'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Amount'];
        }
        return $G_value;
    }


    protected function set_Total_Amount_N($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Amount_N'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Amount_N'];
        }
        return $G_value;
    }

    protected function set_Total_Small($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Small'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Small'];
        }
        return $G_value;
    }


    protected function set_Total_Small_N($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\LoanService::get_instance();
        $ret = $Service->get_all_history_data($year, $month, $data['all_name']);
        $G_value = 0;
        $has_this_data = 0;
        if ($ret) {
            foreach ($ret as $value) {
                if ($year == $value['year'] && $month == $value['month']) {
                    $has_this_data = 1;
                }
                $G_value += $value['Month_Small_N'];
            }
        }
        if (!$has_this_data) {
            $G_value += $data['Month_Small_N'];
        }
        return $G_value;
    }

}