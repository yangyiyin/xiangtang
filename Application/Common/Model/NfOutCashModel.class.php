<?php
/**
 * Created by newModule.
 * Time: 2017-08-16 13:54:40
 */
namespace Common\Model;
use Think\Model;
class NfOutCashModel extends NfBaseModel {
    const STATUS_READY = 0;
    const STATUS_NORMAL = 1;
    const STATUS_REJECT = 99;
    const STATUS_COMPLETE = 3;
    public static $status_map = [self::STATUS_READY=>'已提交', self::STATUS_NORMAL=>'已通过审核', self::STATUS_REJECT=>'已拒绝', self::STATUS_COMPLETE=>'已完成'];

    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}