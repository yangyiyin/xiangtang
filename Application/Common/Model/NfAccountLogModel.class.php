<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 15:56:48
 */
namespace Common\Model;
use Think\Model;
class NfAccountLogModel extends NfBaseModel {
    const TYPE_PLATFORM_ADD = 1;
    const TYPE_PLATFORM_MINUS = 2;
    const TYPE_FRANCHISEE_ADD = 3;
    const TYPE_FRANCHISEE_MINUS = 4;
    const TYPE_INVITER_ADD = 5;
    const TYPE_INVITER_MINUS = 6;
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}