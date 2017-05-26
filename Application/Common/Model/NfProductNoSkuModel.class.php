<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfProductNoSkuModel extends Model {


    protected $_validate = array(
        array('pid', 'require', '产品id不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('product_no', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT)
    );


}