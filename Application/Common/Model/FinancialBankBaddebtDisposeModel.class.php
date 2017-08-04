<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:12:23
 */
namespace Common\Model;
use Think\Model;
class FinancialBankBaddebtDisposeModel extends NfBaseModel {
    protected $patchValidate = true;
    const TYPE_A = 1;
    const TYPE_B = 2;
    const TYPE_C = 3;
    const TYPE_D = 4;
    const TYPE_E = 5;
    const TYPE_F = 6;
    const TYPE_G = 7;
    const TYPE_H = 8;

    public static $RECOVER_METHOD_MAP = [
        self::TYPE_A => '现金收回',
        self::TYPE_B => '上划',
        self::TYPE_C => '以资抵债',
        self::TYPE_D => '重组上调',
        self::TYPE_E => '资产证券化',
        self::TYPE_F => '转让',
        self::TYPE_G => '核销',
        self::TYPE_H => '其他'
    ];

    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Enterprise', 'require', '请检企业名称格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Principal', 'require', '请检查法人代表格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Area', 'number', '请检查企业所属乡镇格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover', 'currency', '请检查收回不良贷款金额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Time', 'number', '请检查收回不良贷款时间格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recover_Method', 'number', '请检查收回不良贷款方式格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );


}