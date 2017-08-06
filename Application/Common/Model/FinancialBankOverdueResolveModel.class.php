<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:16:18
 */
namespace Common\Model;
use Think\Model;
class FinancialBankOverdueResolveModel extends NfBaseModel {
    protected $patchValidate = true;


    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Enterprise', 'require', '请检企业名称格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Principal', 'require', '请检查法人代表格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Area', 'number', '请检查企业所属乡镇格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Overdue', 'currency', '请检查逾期金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Resolve', 'currency', '请检查化解金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );
}