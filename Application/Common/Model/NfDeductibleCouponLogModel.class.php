<?php
/**
 * Created by newModule.
 * Time: 2018-01-09 15:38:05
 */
namespace Common\Model;
use Think\Model;
class NfDeductibleCouponLogModel extends NfBaseModel {
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}