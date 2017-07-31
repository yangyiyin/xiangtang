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

  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
      array('all_name', 'require', '全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('address', 'require', '地址不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('principal', 'require', '主要负责人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('phone', 'require', '电话不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
      array('fixed_phone', 'is_fixed_phone_num', '请填写正确的固定电话', self::EXISTS_VALIDATE, 'function', self::MODEL_BOTH),
      array('tel_phone','is_tel_num','请填写正确的手机号！',self::EXISTS_VALIDATE,'function',self::MODEL_BOTH),
      array('filler_man', 'require', '报表联系人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('total_assets', 'currency', '请检查总资产格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('capital', 'currency', '请检查注册资本格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//      array('staffs', 'number', '请检查员工人数格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

  );
}