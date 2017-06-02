<?php
/**
 * Created by newModule.
 * Time: 2017-06-01 17:33:07
 */
namespace Common\Model;
use Think\Model;
class NfServicesModel extends NfBaseModel {
    protected $_validate = array(
        array('title', 'require', '名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT)
    );
}