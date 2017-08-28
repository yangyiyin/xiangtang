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
    const TYPE_TRADE_MINUS = 10;
    const TYPE_OFFICIAL_ADD = 11;
    const TYPE_OFFICIAL_MINUS = 12;

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
        self::TYPE_TRADE_MINUS => '交易支出',
        self::TYPE_OFFICIAL_ADD => '官方充值',
        self::TYPE_OFFICIAL_MINUS => '官方扣除'
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