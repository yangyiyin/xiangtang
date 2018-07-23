<?php
/**
 * Created by newModule.
 * Time: 2017-08-16 13:54:40
 */
namespace Common\Model;
use Think\Model;
class NfPageStatisticsModel extends NfBaseModel {
    public static $status_map = ['click'=>'查看数', 'share'=>'分享数', 'submit'=>'报名数'];

    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}