<?php
/**
 * Created by newModule.
 * Time: 2017-07-28 19:20:13
 */
namespace Common\Model;
use Think\Model;
class FinancialDepartmentModel extends NfBaseModel {

    const TYPE_FinancialInsuranceProperty = 1;
    const TYPE_FinancialInsuranceLife = 2;
    const TYPE_FinancialInsuranceMutual = 3;
    const TYPE_FinancialVouch = 4;
    const TYPE_FinancialInvestment = 5;
    const TYPE_FinancialInvestmentManager = 6;
    const TYPE_FinancialFutures = 7;
    const TYPE_FinancialLease = 8;
    const TYPE_FinancialLoan = 9;
    const TYPE_FinancialSecurities = 10;
    const TYPE_FinancialTransferFunds = 11;
    const TYPE_FinancialBank = 12;

    const SUB_TYPE_A = 1;
    const SUB_TYPE_B = 2;
    const SUB_TYPE_C = 3;
    const SUB_TYPE_D = 4;
    const SUB_TYPE_E = 5;
    const SUB_TYPE_F = 6;
    const SUB_TYPE_G = 7;

    public static $SUB_TYPE_MAP = [
        self::SUB_TYPE_A => '政策性银行',
        self::SUB_TYPE_B => '大型银行',
        self::SUB_TYPE_C => '股份制商业银行',
        self::SUB_TYPE_D => '邮储银行',
        self::SUB_TYPE_E => '农商行',
        self::SUB_TYPE_F => '城市商业银行',
        self::SUB_TYPE_G => '村镇银行'
    ];



  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
      array('all_name', 'require', '全称为空或该公司全称已录入', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

      array('address', 'require', '地址不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('principal', 'require', '主要负责人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('phone', 'require', '电话不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('fixed_phone', 'is_fixed_phone_num', '请填写正确的固定电话', self::EXISTS_VALIDATE, 'function', self::MODEL_BOTH),
      array('tel_phone','is_tel_num','请填写正确的手机号！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
      array('filler_man', 'require', '报表联系人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('filler_man_tel', 'require', '报表联系人电话不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

      array('total_assets', 'currency', '请检查总资产格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('capital', 'currency', '请检查注册资本格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('staffs', 'number', '请检查员工人数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('build_time', 'require', '请检查成立时间格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('sub_type', 'number', '请检查类型格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

  );
    protected $_auto = array (
        array('build_time','set_build_time',self::MODEL_BOTH,'callback'),
    );
    protected function set_build_time($data){

         return strtotime($data);
    }
}