<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 12:47:42
 */
namespace Common\Model;
use Think\Model;
class FinancialVerifyModel extends NfBaseModel {

   const TYPE_BANK_MONTH = 1;
    const TYPE_BANK_A = 2;
    const TYPE_BANK_B = 3;
    const TYPE_BANK_C = 4;

    const TYPE_Insurance_PROP = 5;
    const TYPE_Insurance_LIFE = 6;
    const TYPE_Insurance_Mutual= 7;

}