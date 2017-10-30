<?php
/**
 * Created by newModule.
 * Time: 2017-06-08 12:20:24
 */
namespace Common\Model;
use Think\Model;
class NfArticleModel extends NfBaseModel {
    const TYPE_NEWS = 'news';
    const TYPE_RULES = 'rules';
    const TYPE_WORKINFO = 'workinfo';
    const TYPE_WORKAPPLY = 'workapply';
    const TYPE_ABOUT = 'about';
    const TYPE_CONTACT = 'contact';
    const TYPE_PUBLIC = 'public';
    const TYPE_HELP = 'help';
    const TYPE_VOLUNTEER_AGREE = 'volunteer_agree';
    const TYPE_DISABLED_HELP_AGREE = 'disabled_help_agree';
    public static $type_map = ['news'=>'新闻','rules'=>'政策法规','workinfo'=>'招聘信息','workapply'=>'求职信息'];


    const STATUS_NORMAL = 1;
    const STATUS_SUBMIT = 2;//待审核
    const STATUS_REJECT = 3;//拒绝
    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
         */
    );

}