<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class OrderSnapshotService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfOrderSnapshot = D('NfOrderSnapshot');
        if ($NfOrderSnapshot->add($data)) {
            return result(TRUE, '', $NfOrderSnapshot->getLastInsID());
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id) {
        $NfOrderSnapshot = D('NfOrderSnapshot');
        return $NfOrderSnapshot->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfOrderSnapshot = D('NfOrderSnapshot');

        if ($NfOrderSnapshot->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderSnapshot->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfOrderSnapshot = D('NfOrderSnapshot');
        $ret = $NfOrderSnapshot->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderSnapshot->getError());
        }
    }



    public function add_batch($data) {
        $NfOrderSnapshot = D('NfOrderSnapshot');
        if ($NfOrderSnapshot->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfOrderSnapshot = D('NfOrderSnapshot');
        $data = [];
        $count = $NfOrderSnapshot->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfOrderSnapshot->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }

    public function get_by_order_ids($order_ids) {

        if (!check_num_ids($order_ids)) {
            return false;
        }
        $NfOrderSnapshot = D('NfOrderSnapshot');
        return $NfOrderSnapshot->where('order_id in ('. join(',', $order_ids) .')')->select();
    }


}