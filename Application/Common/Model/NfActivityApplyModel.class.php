<?php
/**
 * Created by newModule.
 * Time: 2017-10-30 13:47:59
 */
namespace Common\Model;
use Think\Model;
class NfActivityApplyModel extends NfBaseModel {
    const STATUS_SUBMIT = 1;
    const STATUS_OK = 2;
    const STATUS_REJECT = 3;
    const STATUS_SIGN = 4;

    public static $status_map = [1=>'已提交',2=>'审核通过',3=>'已拒绝',4=>'已签到'];
  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}