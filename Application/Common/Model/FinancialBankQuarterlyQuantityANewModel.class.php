<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialQuarterlyQuantityANewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('A1', 'currency', '贷款利率发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A1', 'currency', '贷款利率加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A2', 'currency', '最高利率发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A2', 'currency', '最高利率加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A3', 'currency', '最低利率发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A3', 'currency', '最低利率加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A4', 'currency', '信用发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A4', 'currency', '信用加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A5', 'currency', '抵押发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A5', 'currency', '抵押加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A6', 'currency', '保证发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A6', 'currency', '保证加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('A7', 'currency', '抵押+保证发生额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Rate_A7', 'currency', '抵押+保证加权平均利率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),



    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}