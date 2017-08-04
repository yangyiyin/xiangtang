<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 17:20:08
 */
namespace Common\Model;
use Think\Model;
class FinancialTransferFundsModel extends NfBaseModel {

    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Bank', 'require', '转贷银行不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Account', 'require', '账号不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Unit', 'require', '转贷单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Legal_Person', 'require', '法人代表不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Amount', 'currency', '请检查转贷金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('S_Date', 'number', '请检查起始日格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('E_Date', 'number', '请检查到期日格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Days', 'number', '请检查天数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );
}