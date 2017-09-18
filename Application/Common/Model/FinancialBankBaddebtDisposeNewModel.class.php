<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankBaddebtDisposeNewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Enterprise', 'require', '企业名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans', 'currency', '贷款余额（万元）', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover', 'currency', '收回金额+', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot', 'currency', '总的', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_1', 'currency', '以资抵债', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_2', 'currency', '法院清收', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_3', 'currency', '核销', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_4', 'currency', '上划', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_5', 'currency', '政策性剥离', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_6', 'currency', '打包出售', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_other_name', 'require', '其他', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Ot_other', 'currency', '其他', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),



    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}