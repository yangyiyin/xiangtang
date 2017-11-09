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
    const TYPE_LAUGH = 'laugh';

    const FROM_ADMIN = 1;
    const FROM_CUSTOM = 2;

    const STATUS_INIT = 0;
    const STATUS_OK = 1;
    const STATUS_REJECT = 2;

    public static $status_map = [
        0=>'待审核',
        1=>'中稿',
        2=>'未中稿'
    ];

    public static $status_from_map = [
        '全部'=>0,
        '后台发布'=>1,
        '前端用户投稿'=>2
    ];

    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
         */
    );

}