<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class OrderItemService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfOrderItem = D('NfOrderItem');

        if ($NfOrderItem->add($data)) {
            return result(TRUE, '', $NfOrderItem->getLastInsID());
        } else {
            return result(FALSE, $NfOrderItem->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfOrderItem = D('NfOrderItem');
        return $NfOrderItem->where('id = ' . $id)->find();
    }

    public function get_by_order_id($order_id) {
        $NfOrderItem = D('NfOrderItem');
        return $NfOrderItem->where('order_id = ' . $order_id)->select();
    }
    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfOrderItem = D('NfOrderItem');

        if ($NfOrderItem->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItem->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfOrderItem = D('NfOrderItem');
        $ret = $NfOrderItem->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItem->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfOrderItem = D('NfOrderItem');
        $ret = $NfOrderItem->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItem->getError());
        }
    }


    public function add_batch($data) {
        $NfOrderItem = D('NfOrderItem');
        if ($NfOrderItem->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfOrderItem = D('NfOrderItem');
        $data = [];
        $count = $NfOrderItem->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfOrderItem->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }


}