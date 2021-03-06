<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfOrderModel extends Model {

    const STATUS_SUBMIT = 1;//已提交
    const STATUS_PAY = 2;//已付款
    const STATUS_STOCK_OUT = 3;//已出库
    const STATUS_SENDING = 4;//已发货已派送
    const STATUS_DONE = 5;//已完成
    const STATUS_CANCEL = 6;//已取消
    const STATUS_PAYING = 7;//支付中
    const STATUS_RECEIVED = 8;//已接单

    const TYPE_ORDER_FACTORY = 1;
    const TYPE_ORDER_MEITUAN = 2;
    const TYPE_ORDER_SHOP = 3;
    const TYPE_ORDER_PEOPLE = 4;

    const RECIEVE_TYPE_ARRIVE = 1;
    const RECIEVE_TYPE_SERVER = 2;

    public static $type_map = [
        1 => '工厂订单',
        2 => '美团订单',
        3 => '排挡订单',
        4 => '个人订单'
    ];

    public static $status_map = [
        1 => '已提交',
        2 => '已付款',
        3 => '已出库',
        4 => '已发货',
        5 => '已完成',
        6 => '已取消',
        7 => '支付中',
        8 => '已接单'
    ];

}