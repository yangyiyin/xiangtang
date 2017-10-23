<?php
/**
 * Created by newModule.
 * Time: 2017-06-08 12:20:24
 */
namespace Common\Model;
use Think\Model;
class NfArticleModel extends NfBaseModel {
    const TYPE_NEWS = 'news';
    const TYPE_ABOUT = 'about';
    const TYPE_CONTACT = 'contact';
    const TYPE_PUBLIC = 'public';
    public static $type_map = ['news'=>'新闻','public'=>'公告'];
    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
         */
    );

}