<?php
/**
 * Created by newModule.
 * Time: 2017-07-28 15:31:36
 */
namespace Common\Model;
use Think\Model;
class FinancialInsurancePropertyModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income', 'currency', '请检查保费收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('income_yoy', 'currency', '请检查保费收入同比格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_a', 'currency', '请检查企业财产险收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_b', 'currency', '请检查机动车辆险收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_c', 'currency', '请检查其他险收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('reserves', 'currency', '请检查存储金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff', 'currency', '请检查赔付支出格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('payoff_rate', 'currency', '请检查赔付支出率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_a', 'currency', '请检查企业财产险赔付支出格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('payoff_a_rate', 'currency', '请检查企业财产险赔付支出率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_b', 'currency', '请检查企业财产险赔付支出格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('payoff_b_rate', 'currency', '请检查企业财产险赔付支出率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_c', 'currency', '请检查其他险赔付支出格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('payoff_c_rate', 'currency', '请检查其他险赔付支出率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('staff', 'require', '期末在岗人数不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('authorized', 'require', '期末持证人数不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('income_yoy','get_income_yoy',self::MODEL_BOTH,'callback'),
        array('payoff_rate','get_payoff_rate',self::MODEL_BOTH,'callback'),
        array('payoff_a_rate','get_payoff_a_rate',self::MODEL_BOTH,'callback'),
        array('payoff_b_rate','get_payoff_b_rate',self::MODEL_BOTH,'callback'),
        array('payoff_c_rate','get_payoff_c_rate',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function get_income_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $InsurancePropertyService = \Common\Service\InsurancePropertyService::get_instance();
        $last_year_data = $InsurancePropertyService->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['income']) && $last_year_data['income']) {
            $yoy = fix_2(($data['income'] - $last_year_data['income']) / $last_year_data['income']) ;
        }
        return $yoy;
    }



    protected function get_payoff_rate($data) {
        $rate = 100;
        //var_dump($data1);die();
        if ($data['income']) {
            $rate = fix_2($data['payoff'] / $data['income']);
        }

        return $rate;
    }

    protected function get_payoff_a_rate($data) {
        $rate = 100;

        if ($data['income_a']) {
            $rate = fix_2($data['payoff_a'] / $data['income_a']);
        }

        return $rate;
    }
    protected function get_payoff_b_rate($data) {
        $rate = 100;

        if ($data['income_b']) {
            $rate = fix_2($data['payoff_b'] / $data['income_b']);
        }

        return $rate;
    }
    protected function get_payoff_c_rate($data) {
        $rate = 100;

        if ($data['income_c']) {
            $rate = fix_2($data['payoff_c'] / $data['income_c']);
        }

        return $rate;
    }
}