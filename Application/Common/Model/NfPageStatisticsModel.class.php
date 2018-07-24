<?php
/**
 * Created by newModule.
 * Time: 2017-08-16 13:54:40
 */
namespace Common\Model;
use Think\Model;
class NfPageStatisticsModel extends NfBaseModel {
    const type_view = 1;
    const type_share = 2;
    const type_submit = 3;

    public static $status_map = ['1'=>'查看数', '2'=>'分享数', '3'=>'报名数'];

    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}