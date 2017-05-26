<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class OrderItemPreService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfOrderItemPre = D('NfOrderItemPre');
        if ($NfOrderItemPre->add($data)) {
            return result(TRUE, '', $NfOrderItemPre->getLastInsID());
        } else {
            return result(FALSE, $NfOrderItemPre->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfOrderItemPre = D('NfOrderItemPre');
        return $NfOrderItemPre->where('id = ' . $id)->find();
    }

    public function get_by_oid($oid) {
        $NfOrderItemPre = D('NfOrderItemPre');
        return $NfOrderItemPre->where('order_pre_id = ' . $oid)->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfOrderItemPre = D('NfOrderItemPre');

        if ($NfOrderItemPre->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItemPre->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfOrderItemPre = D('NfOrderItemPre');
        $ret = $NfOrderItemPre->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItemPre->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfOrderItemPre = D('NfOrderItemPre');
        $ret = $NfOrderItemPre->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderItemPre->getError());
        }
    }


    public function add_batch($data) {
        $NfOrderItemPre = D('NfOrderItemPre');
        if ($NfOrderItemPre->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


}