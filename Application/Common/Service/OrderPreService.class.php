<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class OrderPreService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfOrderPre = D('NfOrderPre');
        if ($NfOrderPre->add($data)) {
            return result(TRUE, '', $NfOrderPre->getLastInsID());
        } else {
            return result(FALSE, $NfOrderPre->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfOrderPre = D('NfOrderPre');
        return $NfOrderPre->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfOrderPre = D('NfOrderPre');

        if ($NfOrderPre->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderPre->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfOrderPre = D('NfOrderPre');
        $ret = $NfOrderPre->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderPre->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfOrderPre = D('NfOrderPre');
        $ret = $NfOrderPre->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrderPre->getError());
        }
    }


    public function add_batch($data) {
        $NfOrderPre = D('NfOrderPre');
        if ($NfOrderPre->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function get_order_pre_no($uid) {
        return 'P'.$uid.getMillisecond().mt_rand(0,9).mt_rand(0,9);
    }

    public function get_by_ids($ids) {
        $NfOrderPre = D('NfOrderPre');
        $where = [];
        $where['id'] = ['in', $ids];
        return $NfOrderPre->where($where)->select();
    }
}