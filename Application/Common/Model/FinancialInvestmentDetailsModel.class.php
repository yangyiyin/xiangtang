<?php
/**
 * Created by newModule.
 * Time: 2017-08-02 11:11:25
 */
namespace Common\Model;
use Think\Model;
class FinancialInvestmentDetailsModel extends NfBaseModel {
    protected $patchValidate = true;
    const TYPE_A = 1;
    const TYPE_B = 2;
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Name', 'require', '所管理公司名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Area', 'number', '请检查区域信息是否正确', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Amount', 'currency', '请检查管理金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );


}