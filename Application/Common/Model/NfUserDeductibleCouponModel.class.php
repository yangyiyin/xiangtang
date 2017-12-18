<?php
/**
 * Created by newModule.
 * Time: 2017-12-13 10:23:27
 */
namespace Common\Model;
use Think\Model;
class NfUserDeductibleCouponModel extends NfBaseModel {
    const STATUS_OK = 1;//有效
    const STATUS_NONE = 99;//失效
    public static $status_map = [1=>'有效',99=>'失效'];
    protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
         */
    );
}