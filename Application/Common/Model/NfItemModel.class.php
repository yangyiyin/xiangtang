<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfItemModel extends Model {
    const STATUS_READY = 0;
    const STATUS_NORAML = 1;
    const STATUS_SUBMIT = 2;
    const STATUS_DELETE = 99;

    public static $status_map = [0=>'已创建', 1=>'正常',99=>'已下架', 2=>'待审核'];

    protected $_validate = array(
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('pid', 'require', 'pid不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
    );


}