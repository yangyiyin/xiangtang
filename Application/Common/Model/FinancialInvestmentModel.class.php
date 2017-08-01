<?php
/**
 * Created by newModule.
 * Time: 2017-08-01 08:14:09
 */
namespace Common\Model;
use Think\Model;
class FinancialInvestmentModel extends NfBaseModel {
    const TYPE_A = 1;
    const TYPE_B = 2;
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


        array('Amount', 'currency', '请检查资金规模格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Projects', 'number', '请检机构数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Tax_B', 'currency', '请检查营业税格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Tax_I', 'currency', '请检查所得税格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Tax_O', 'currency', '请检查其他税费格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('Staff','set_Staff',self::MODEL_BOTH,'callback')

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function set_Staff($data) {
        //获得去年的income
        return array_sum(explode(',', $data['Staff_Sub']));
    }
}