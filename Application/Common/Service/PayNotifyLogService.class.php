<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class PayNotifyLogService extends BaseService{
    public static $page_size = 20;
    public function add_one($data) {
        $NfPayNotifyLog = D('NfPayNotifyLog');
        if ($NfPayNotifyLog->add($data)) {
            return result(TRUE, '', $NfPayNotifyLog->getLastInsID());
        } else {
            return result(FALSE, $NfPayNotifyLog->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfPayNotifyLog = D('NfPayNotifyLog');
        return $NfPayNotifyLog->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfPayNotifyLog = D('NfPayNotifyLog');

        if ($NfPayNotifyLog->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfPayNotifyLog->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfPayNotifyLog = D('NfPayNotifyLog');
        $ret = $NfPayNotifyLog->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfPayNotifyLog->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfPayNotifyLog = D('NfPayNotifyLog');
        $ret = $NfPayNotifyLog->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfPayNotifyLog->getError());
        }
    }


    public function add_batch($data) {
        $NfPayNotifyLog = D('NfPayNotifyLog');
        if ($NfPayNotifyLog->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfPayNotifyLog = D('NfPayNotifyLog');
        $data = [];
        $count = $NfPayNotifyLog->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfPayNotifyLog->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }



}