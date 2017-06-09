<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfUserModel extends Model {
    const TYPE_FACTORY = 1;
    const TYPE_MEITUAN = 2;
    const TYPE_SHOP = 3;
    const TYPE_PEOPLE = 4;

    const STATUS_NORAML = 1;
    const STATUS_VERIFY = 2;
    const STATUS_FORBID = 99;

    const VERIFY_STATUS_NONE = 0;
    const VERIFY_STATUS_SUBMIT = 1;
    const VERIFY_STATUS_OK = 2;
    const VERIFY_STATUS_REJECT = 3;
    public static $type_map = [
        1=>'factory',
        2=>'meituan',
        3=>'shop',
        4=>'people'
    ];
    public static $type_desc_map = [
        1=>'工厂',
        2=>'美团',
        3=>'排挡',
        4=>'个人'
    ];
    public static $status_map = [
        1=>'正常',
        2=>'待审核',
        99=>'禁用'
    ];

    protected $_validate = array(
        //array('type', array(1,2,3,4), '用户类型不正确！', self::EXISTS_VALIDATE , 'in', self::MODEL_INSERT)
    );
}