<?php
/**
 * Created by newModule.
 * Time: 2017-12-22 11:46:10
 */
namespace Common\Model;
use Think\Model;
class NfArticleClicksModel extends NfBaseModel {
    const TYPE_LIKE = 1;
    const TYPE_COLLECT = 2;
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}