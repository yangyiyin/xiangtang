<?php
/**
 * Created by newModule.
 * Time: 2017-07-28 15:30:56
 */
namespace Common\Model;
use Think\Model;
class FinancialInsuranceLifeModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('income', 'currency', '请检查保费收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_a', 'currency', '请检查个人营销格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_a_a', 'currency', '请检查新单首期格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_a_b', 'currency', '请检查新单期缴格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_b', 'currency', '请检查团体业务格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('income_c', 'currency', '请检查银行代理格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_a', 'currency', '请检查短险赔款金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_b', 'currency', '请检查死伤医给付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_c', 'currency', '请检查满期给付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('payoff_d', 'currency', '请检查年金给付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('backoff', 'currency', '请检查退保金额支出格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('staff', 'require', '期末在岗人数不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('authorized', 'require', '期末持证人数不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('income_a_yoy','get_income_a_yoy',self::MODEL_BOTH,'callback'),
        array('payoff_a_rate','get_payoff_a_rate',self::MODEL_BOTH,'callback')

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function get_income_a_yoy($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\InsuranceLifeService::get_instance();
        $last_year_data = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        $yoy = 0;
        if (isset($last_year_data['income_a']) && $last_year_data['income_a']) {
            $yoy = fix_2(($data['income_a'] - $last_year_data['income_a']) / $last_year_data['income_a']) ;
        }
        return $yoy;
    }


    protected function get_payoff_a_rate($data) {
        $rate = 100;

        if ($data['income']) {
            $rate = fix_2($data['payoff_a'] / $data['income']);
        }

        return $rate;
    }

}