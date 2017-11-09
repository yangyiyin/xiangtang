<?php
/**
 * Created by newModule.
 * Time: 2017-07-22 15:56:48
 */
namespace Common\Model;
use Think\Model;
class NfAccountLogModel extends NfBaseModel {
    const TYPE_PLATFORM_ADD = 1;
    const TYPE_PLATFORM_MINUS = 2;
    const TYPE_FRANCHISEE_ADD = 3;
    const TYPE_FRANCHISEE_MINUS = 4;
    const TYPE_INVITER_ADD = 5;
    const TYPE_INVITER_MINUS = 6;
    const TYPE_DEALER_ADD = 7;
    const TYPE_DEALER_MINUS = 8;
    const TYPE_OUT_CASH_MINUS = 9;

    const TYPE_PUBLISH = 10;//投稿
    const TYPE_PUBLISH_OK = 11;//中稿
    const TYPE_SHARE = 12;//分享
    const TYPE_LIKED = 13;//被赞

    public static $TYPE_MAP = [
        self::TYPE_PLATFORM_ADD => '',
        self::TYPE_PLATFORM_MINUS => '',
        self::TYPE_FRANCHISEE_ADD => '',
        self::TYPE_FRANCHISEE_MINUS => '',
        self::TYPE_INVITER_ADD => '分佣者收入',
        self::TYPE_INVITER_MINUS => '分佣者支出',
        self::TYPE_DEALER_ADD => '经销商佣金收入',
        self::TYPE_DEALER_MINUS => '经销商佣金支出',
        self::TYPE_OUT_CASH_MINUS => '提现',
        self::TYPE_PUBLISH => '投稿',
        self::TYPE_PUBLISH_OK => '中稿',
        self::TYPE_SHARE => '分享',
        self::TYPE_LIKED => '被赞'
    ];

    public static $TYPE_VALUE_MAP = [
        self::TYPE_PUBLISH => 20,
        self::TYPE_PUBLISH_OK => 100,
        self::TYPE_SHARE => 20,
        self::TYPE_LIKED => 10
    ];


  protected $_validate = array(
        /**
        array('title', 'require', '名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('cid', 'require', '分类不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('img', 'require', '图片不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT),
        array('unit_desc', 'require', '单位不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_INSERT)
        */
    );
}