<?php
/**
 * Created by newModule.
 * Time: 2017-08-21 16:41:14
 */
namespace Common\Model;
use Think\Model;
class NfVolunteerModel extends NfBaseModel {

    const STATUS_BACK = 0;
    const STATUS_SUBMIT = 1;
    const STATUS_PAYING = 2;
    const STATUS_PAYED = 3;
    const STATUS_OK = 4;

    public static $status_map = [0=>'已退回',1=>'已提交',2=>'支付中',3=>'已支付',4=>'已通过'];

  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}