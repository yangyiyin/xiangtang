<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:15:44
 */
namespace Common\Model;
use Think\Model;
class FinancialBankLoanDetailModel extends NfBaseModel {


    protected $patchValidate = true;
    const TYPE_A = 1;
    const TYPE_B = 2;
    const TYPE_C = 3;
    const TYPE_D = 4;
    const TYPE_E = 5;


    public static $PATTERN_MAP = [
        self::TYPE_A => '正常',
        self::TYPE_B => '关注',
        self::TYPE_C => '次级',
        self::TYPE_D => '可疑',
        self::TYPE_E => '损失',

    ];

    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Contract', 'require', '请检合同编号格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Enterprise', 'require', '请检查企业名称格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans', 'currency', '请检查贷款余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Interest', 'currency', '请检查执行年利率格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Principal', 'require', '请检查法人代表格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Phone', 'require', '请检查联系电话格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Address', 'require', '请检查注册地址格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Area', 'number', '请检查所属镇格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Industry', 'require', '请检查所属行业格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Startdate', 'number', '请检查发放日期:格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Enddate', 'number', '请检查到期日期格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Guarantee', 'require', '请检查担保方式格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Over_Credit', 'currency', '请检查信用余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Over_Mortgage', 'currency', '请检查抵押余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Over_Pledge', 'currency', '请检查质押余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Over_Margin', 'currency', '请检查保证余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Guarantor', 'require', '请检查保证人格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Pattern', 'number', '请检查五级形态格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('OverdueDays', 'number', '请检查本金逾期天数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Coordination', 'require', '请检查是否需要地方政府协调配合格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );
}