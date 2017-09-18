<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankBaddebtNewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Baddebt_Initial', 'currency', '年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Month', 'currency', '本月', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Initial_Modify', 'currency', ' 比年初增减', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Lastmon_Modify', 'currency', ' 比上月增减', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_B', 'currency', ' 关注', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_B_Initial_Modify', 'currency', ' 关注比年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_C', 'currency', ' 次级', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_C_Initial_Modify', 'currency', ' 次级比年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_D', 'currency', ' 可疑', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_D_Initial_Modify', 'currency', ' 可疑比年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_E', 'currency', ' 损失', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_E_Initial_Modify', 'currency', ' 损失比年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_CDE', 'currency', ' 次级+可疑+损失', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Initial_Rate', 'currency', '年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Month_Rate', 'currency', '本月', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Year_New', 'currency', '当年新发生', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back', 'currency', '收回合计', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_Cash_Lastyear', 'currency', '现金收回 上年度', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_Cash_New', 'currency', '现金收回 当年新发生', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_A', 'currency', '上滑', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_B', 'currency', '以资抵债', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_C', 'currency', '重组上调', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_D', 'currency', '资产债券化', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_E', 'currency', '转让', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Back_F', 'currency', '核销', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Overdue', 'currency', '逾期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Overdue_Initial_Modify', 'currency', '逾期比年初', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Baddebt_Overdue_long', 'currency', '逾期3月以上', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}