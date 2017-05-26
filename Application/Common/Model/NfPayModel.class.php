<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:42
 */
namespace Common\Model;
use Think\Model;
class NfPayModel extends Model {

    const STATUS_SUBMIT = 1;//已提交
    const STATUS_COMPLETE = 2;//已完成
    const STATUS_CLOSED = 3;//已关闭
    const STATUS_DELETE = 99;//已删除

    const PAY_AGENT_ALIPAY = 'alipay';

}