<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankCreditNewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Loans', 'require', '贷款余额	亿元', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_A', 'require', '小企业贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_B', 'require', '担保公司担保贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_C', 'require', '政府平台公司贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_D', 'require', '涉农贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_D1', 'require', '农业贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_D2', 'require', '农户贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_E', 'require', '固定资产贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_F', 'require', '房地产开发贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_G', 'require', '个人住房贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_G1', 'require', '个人住房按揭贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_H', 'require', '个人经营性贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_I', 'require', '票据', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_I1', 'require', '银票贴现', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_I2', 'require', '商票贴现', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_J', 'require', '节能减排', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loans_K', 'require', '高新技术', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits', 'require', '存款余额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_A', 'require', '对公', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_A1', 'require', '活期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_A2', 'require', '定期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_B', 'require', '储蓄', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_B1', 'require', '活期储蓄', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_B2', 'require', '定期储蓄', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_C', 'require', '保证金', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_C1', 'require', '开证保证金', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_C2', 'require', '签发银票保证金', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_C3', 'require', '商票保贴保证金', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_C4', 'require', '开立保函保证金', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D', 'require', '其他存款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D1', 'require', '同业存款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D2', 'require', '银行承税质押的存款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D3', 'require', '转(再)贴现', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D4', 'require', '银行对外担保和承诺金额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D41', 'require', '银行开出保函', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D42', 'require', '银行承税汇票余额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D43', 'require', '信用证余额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D44', 'require', '其他对外承诺', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D5', 'require', '信贷资产', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D51', 'require', '证券化', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D52', 'require', '贷款转让', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D53', 'require', '银信合作转让', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D6', 'require', '委托代理贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D61', 'require', '委托贷款', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D62', 'require', '代理他行', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D63', 'require', '代理他行集约票据', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D7', 'require', '理财业务余额', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D8', 'require', '国际业务结算量', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D9', 'require', '账面利润', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D10', 'require', '中间业务收入', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposits_D11', 'require', '利息收入', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),



    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}