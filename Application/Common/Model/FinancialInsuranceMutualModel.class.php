<?php
/**
 * Created by newModule.
 * Time: 2017-07-28 15:14:41
 */
namespace Common\Model;
use Think\Model;
class FinancialInsuranceMutualModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Life_A', 'number', '请检查家庭财产保险承保件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Life_B', 'number', '请检查家庭财产保险承保户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Life_C', 'currency', '请检查家庭财产保险保费格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Life_D', 'currency', '请检查家庭财产保险保险金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Life_E', 'number', '请检查家庭财产保险赔付件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Life_F', 'currency', '请检查家庭财产保险赔付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_A', 'number', '请检查意外险承保件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_B', 'number', '请检查意外险承保户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_C', 'currency', '请检查意外险保费格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_D', 'currency', '请检查意外险保险金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_E', 'number', '请检查意外险赔付件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Casualty_F', 'currency', '请检查意外险赔付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_A', 'number', '请检查补充医疗互助保险承保件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_B', 'number', '请检查补充医疗互助保险承保户数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_C', 'currency', '请检查补充医疗互助保险保费格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_D', 'currency', '请检查补充医疗互助保险保险金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_E', 'number', '请检查补充医疗互助保险赔付件数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Medical_F', 'currency', '请检查补充医疗互助保险赔付金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback')

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}