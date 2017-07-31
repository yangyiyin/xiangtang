<?php
/**
 * Created by newModule.
 * Time: 2017-07-31 14:53:05
 */
namespace Common\Model;
use Think\Model;
class FinancialVouchModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


        array('C_Balance', 'currency', '请检查本期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Quantity', 'number', '请检本期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('C_Balance_Ly', 'currency', '请检查去年同期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('C_Quantity_Ly', 'number', '请检查去年同期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('G_Balance', 'currency', '请检查本年余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('G_Quantity', 'number', '请检查本年笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Balance_Ly', 'currency', '请检查去年同期余额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Quantity_Ly', 'number', '请检查去年同期笔数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Income', 'currency', '请检查本月收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Income', 'currency', '请检查本年收入格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Recover', 'currency', '请检查本月收回格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Recover', 'currency', '请检查本年收回格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Profit', 'currency', '请检查本期利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Profit', 'currency', '请检查本年利润格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Taxable', 'currency', '请检查本期纳税总额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Taxable', 'currency', '请检查本年纳税总额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('C_Vouch', 'currency', '请检查本期担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Vouch', 'currency', '请检查本年担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('G_Vouch_Ly', 'currency', '请检查本年同期担保额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('C_Balance_Ly','set_C_Balance_Ly',self::MODEL_BOTH,'callback'),
        array('C_Quantity_Ly','set_C_Quantity_Ly',self::MODEL_BOTH,'callback'),
        array('G_Quantity_Ly','set_G_Quantity_Ly',self::MODEL_BOTH,'callback'),
        array('G_Income','set_G_Income',self::MODEL_BOTH,'callback'),
        array('G_Recover','set_G_Recover',self::MODEL_BOTH,'callback'),
        array('G_Vouch','set_G_Vouch',self::MODEL_BOTH,'callback'),
        array('G_Vouch_Ly','set_G_Vouch_Ly',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

}