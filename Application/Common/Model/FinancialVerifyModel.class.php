<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 12:47:42
 */
namespace Common\Model;
use Think\Model;
class FinancialVerifyModel extends NfBaseModel {

    const STATUS_INIT = 0;
    const STATUS_SUBMIT = 1;
    const STATUS_OK = 2;

    public static $status_map = [0=>'草稿',1=>'已提交',2=>'审核通过'];

    const TYPE_BANK_MONTH = 1;
    const TYPE_BANK_A = 2;
    const TYPE_BANK_B = 3;
    const TYPE_BANK_C = 4;

    const TYPE_FinancialInsuranceProperty = 13;
    const TYPE_FinancialInsuranceLife = 14;
    const TYPE_FinancialInsuranceMutual = 15;
    const TYPE_FinancialVouch = 16;
    const TYPE_FinancialInvestment = 5;
    const TYPE_FinancialInvestmentManager = 6;
    const TYPE_FinancialFutures = 7;
    const TYPE_FinancialLease = 8;
    const TYPE_FinancialLoan = 9;
    const TYPE_FinancialSecurities = 10;
    const TYPE_FinancialTransferFunds = 11;
    const TYPE_FinancialBank = 12;

}