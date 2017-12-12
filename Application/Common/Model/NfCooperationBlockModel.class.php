<?php
/**
 * Created by newModule.
 * Time: 2017-12-12 10:13:14
 */
namespace Common\Model;
use Think\Model;
class NfCooperationBlockModel extends NfBaseModel {
    const TYPE_PROMOTION = 1;
    const TYPE_RECOMMEND = 2;
    public static $type_map = [self::TYPE_PROMOTION => '促销', self::TYPE_RECOMMEND => '推荐'];
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}