<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:16:54
 */
namespace Common\Model;
use Think\Model;
class FinancialBankQuarterlyModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Deposits', 'currency', '请检各项存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans', 'currency', '请检查各项贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_A', 'currency', '请检查公司贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_A_Sub', 'require', '请检查各公司贷款子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_B_Sub', 'require', '请检按期限分各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_C_Sub', 'require', '请检产业分各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_D_Sub', 'require', '请检按担保方式分各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_E', 'currency', '请检个人贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_E_Sub', 'require', '请检个人贷款各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_F', 'currency', '请检房地产贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_F_Sub', 'require', '请检房地产贷款各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_G', 'currency', '请检支持地方经济发展贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_G_Sub', 'require', '请检支持地方经济发展贷款各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_H', 'currency', '请检贷款质量格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_H_Sub', 'require', '请检贷款质量各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Assets_Total', 'currency', '请检资产总计格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Debt_Total', 'currency', '请检负债总计格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Owner_Total', 'currency', '请检所有者权益格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Profit_Total', 'currency', '请检本年累计利润	格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Tax_Total', 'currency', '请检各类税费格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Other_Item', 'currency', '请检表外项目余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Other_Item_Sub', 'require', '请检各项表外项目余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Staff', 'number', '请检从业人员总数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Staff_Sub', 'require', '请检从业人员各子项格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),



    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback')

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }
}