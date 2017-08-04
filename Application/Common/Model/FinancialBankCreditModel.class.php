<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankCreditModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Deposit_A', 'currency', '请检境内存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A1', 'currency', '请检查住户存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A11', 'currency', '请检查住户存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A12', 'currency', '请检查活期存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A2', 'currency', '请检查非金融企业存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A21', 'currency', '请检活期存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A22', 'currency', '请检定期及其他存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A3', 'currency', '请检广义政府存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A31', 'currency', '请检活期存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A32', 'currency', '请检定期及其他存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_A4', 'currency', '请检非银行业金融机构存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Deposit_B', 'currency', '请检境外存款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A', 'currency', '请检境内贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A1', 'currency', '请检住户贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A11', 'currency', '请检短期贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A111', 'currency', '请检消费贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A112', 'currency', '请检经营贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A12', 'currency', '请检中长期贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A121', 'currency', '请检消费贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A122', 'currency', '请检经营贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A2', 'currency', '请检非金融企业及机关团体贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A21', 'currency', '请检短期贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A22', 'currency', '请检中长期贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A23', 'currency', '请检票据融资格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A24', 'currency', '请检融资租赁格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A25', 'currency', '请检各项垫款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_A3', 'currency', '请检非银行业金融机构贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Loan_B', 'currency', '请检境外贷款格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),





//        Deposits decimal(7,2) NOT NULL DEFAULT 0,	#存款余额	亿元
//	Deposit_A decimal(7,2) NOT NULL DEFAULT 0,	#一、境内存款
//	Deposit_A1 decimal(7,2) NOT NULL DEFAULT 0,	#1、住户存款
//	Deposit_A11 decimal(7,2) NOT NULL DEFAULT 0,	#1.1、住户存款
//	Deposit_A12 decimal(7,2) NOT NULL DEFAULT 0,	#1.2、活期存款
//	Deposit_A2 decimal(7,2) NOT NULL DEFAULT 0,	#2、非金融企业存款
//	Deposit_A21 decimal(7,2) NOT NULL DEFAULT 0,	#2.1、活期存款
//	Deposit_A22 decimal(7,2) NOT NULL DEFAULT 0,	#2.2、定期及其他存款
//	Deposit_A3 decimal(7,2) NOT NULL DEFAULT 0,	#3、广义政府存款
//	Deposit_A31 decimal(7,2) NOT NULL DEFAULT 0,	#3.1、活期存款
//	Deposit_A32 decimal(7,2) NOT NULL DEFAULT 0,	#3.2、定期及其他存款
//	Deposit_A4 decimal(7,2) NOT NULL DEFAULT 0,	#4、非银行业金融机构存款
//	Deposit_B decimal(7,2) NOT NULL DEFAULT 0,	#二、境外存款
//	Deposits_Initial decimal(7,2) NOT NULL DEFAULT 0,	#年初余额
//	Deposits_Lastmon decimal(7,2) NOT NULL DEFAULT 0,	#上月余额
//	Deposits_Lastyear decimal(7,2) NOT NULL DEFAULT 0,	#去年同期
//	Loans decimal(7,2) NOT NULL DEFAULT 0,		#贷款余额
//	Loan_A decimal(7,2) NOT NULL DEFAULT 0,		#境内贷款
//	Loan_A1 decimal(7,2) NOT NULL DEFAULT 0,	#1、住户贷款
//	Loan_A11 decimal(7,2) NOT NULL DEFAULT 0,	#1.1、短期贷款
//	Loan_A111 decimal(7,2) NOT NULL DEFAULT 0,	#1.1.1、消费贷款
//	Loan_A112 decimal(7,2) NOT NULL DEFAULT 0,	#1.1.2、经营贷款
//	Loan_A12 decimal(7,2) NOT NULL DEFAULT 0,	#1.2、中长期贷款
//	Loan_A121 decimal(7,2) NOT NULL DEFAULT 0,	#1.2.1、消费贷款
//	Loan_A122 decimal(7,2) NOT NULL DEFAULT 0,	#1.2.2、经营贷款
//	Loan_A2 decimal(7,2) NOT NULL DEFAULT 0,	#2、非金融企业及机关团体贷款
//	Loan_A21 decimal(7,2) NOT NULL DEFAULT 0,	#2.1、短期贷款
//	Loan_A22 decimal(7,2) NOT NULL DEFAULT 0,	#2.2、中长期贷款
//	Loan_A23 decimal(7,2) NOT NULL DEFAULT 0,	#2.3、票据融资
//	Loan_A24 decimal(7,2) NOT NULL DEFAULT 0,	#2.4、融资租赁
//	Loan_A25 decimal(7,2) NOT NULL DEFAULT 0,	#2.5、各项垫款
//	Loan_A3 decimal(7,2) NOT NULL DEFAULT 0,	#3、非银行业金融机构贷款
//	Loan_B decimal(7,2) NOT NULL DEFAULT 0,		#境外贷款
//	Loan_Initial decimal(7,2) NOT NULL DEFAULT 0,	#年初余额
//	Loan_Lastmon decimal(7,2) NOT NULL DEFAULT 0,	#上月余额
//	Loan_Lastyear decimal(7,2) NOT NULL DEFAULT 0,	#去年同期

    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),
        array('Deposits','set_Deposits',self::MODEL_BOTH,'callback'),
        array('Loans','set_Loans',self::MODEL_BOTH,'callback'),
        array('Deposits_Initial','set_Deposits_Initial',self::MODEL_BOTH,'callback'),
        array('Deposits_Lastmon','set_Deposits_Lastmon',self::MODEL_BOTH,'callback'),
        array('Deposits_Lastyear','set_Deposits_Lastyear',self::MODEL_BOTH,'callback'),
        array('Loan_Initial','set_Loan_Initial',self::MODEL_BOTH,'callback'),
        array('Loan_Lastmon','set_Loan_Lastmon',self::MODEL_BOTH,'callback'),
        array('Loan_Lastyear','set_Loan_Lastyear',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }

    protected function set_Deposits($data) {
        return $data['Deposit_A'] + $data['Deposit_A1'] + $data['Deposit_A11'] + $data['Deposit_A12'] + $data['Deposit_A2'] + $data['Deposit_A21'] + $data['Deposit_A22'] + $data['Deposit_A3'] + $data['Deposit_A31'] + $data['Deposit_A32'] + $data['Deposit_A4'] + $data['Deposit_B'];
    }

    protected function set_Loans($data) {
        return $data['Loan_A'] + $data['Loan_A1'] + $data['Loan_A11'] + $data['Loan_A111'] + $data['Loan_A112'] + $data['Loan_A12'] + $data['Loan_A121'] + $data['Loan_A122'] + $data['Loan_A2'] + $data['Loan_A21'] + $data['Loan_A22'] + $data['Loan_A23'] + $data['Loan_A24'] + $data['Loan_A25'] + $data['Loan_A3'] + $data['Loan_B'];
    }

    protected function set_Deposits_Initial($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        $ret = $Service->get_by_month_year($year - 1, 12, $data['all_name']);

        return isset($ret['Deposits']) ? $ret['Deposits'] : 0;
    }

    protected function set_Deposits_Lastmon($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        if ($month == 1) {
            $ret = $Service->get_by_month_year($year - 1, 12, $data['all_name']);
        } else {
            $ret = $Service->get_by_month_year($year, $month - 1, $data['all_name']);
        }


        return isset($ret['Deposits']) ? $ret['Deposits'] : 0;
    }

    protected function set_Deposits_Lastyear($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        $ret = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        return isset($ret['Deposits']) ? $ret['Deposits'] : 0;
    }


    protected function set_Loan_Initial($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        $ret = $Service->get_by_month_year($year - 1, 12, $data['all_name']);

        return isset($ret['Loans']) ? $ret['Loans'] : 0;
    }

    protected function set_Loan_Lastmon($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        if ($month == 1) {
            $ret = $Service->get_by_month_year($year - 1, 12, $data['all_name']);
        } else {
            $ret = $Service->get_by_month_year($year, $month - 1, $data['all_name']);
        }


        return isset($ret['Loans']) ? $ret['Loans'] : 0;
    }

    protected function set_Loan_Lastyear($data) {
        //获得去年的income
        $year = intval($data['year']);
        $month = intval($data['month']);
        $Service = \Common\Service\BankCreditService::get_instance();
        $ret = $Service->get_by_month_year($year - 1, $month, $data['all_name']);
        return isset($ret['Loans']) ? $ret['Loans'] : 0;
    }

}