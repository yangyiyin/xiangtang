<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankBaddebtDetailNewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Enterprise', 'require', '企业名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Principal', 'require', '法人', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Address', 'require', '注册地址', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans', 'currency', '贷款余额（万元）', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_Type', 'require', '不良贷款分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Industry', 'require', '所属行业', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Reason', 'require', '原因', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Handle', 'require', '清收措施', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Info', 'require', '企业基本情况', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Plan', 'require', '下一步处置计划', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recommend', 'require', '要求或建议', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Startdate', 'require', '列入日期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Enddate', 'require', '收回日期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),




    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}