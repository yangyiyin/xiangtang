<?php
/**
 * Created by newModule.
 * Time: 2017-08-08 21:16:23
 */
namespace Common\Model;
use Think\Model;
class NfOrderBenefitModel extends NfBaseModel {
    const TYPE_ACCOUNT = 1;
    const TYPE_OVERALL = 2;
    const TYPE_COUPON = 3;
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}