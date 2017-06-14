<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class UserCourierService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfUserCourier = D('NfUserCourier');
        if ($NfUserCourier->add($data)) {
            return result(TRUE, '', $NfUserCourier->getLastInsID());
        } else {
            return result(FALSE, $NfUserCourier->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfUserCourier = D('NfUserCourier');
        return $NfUserCourier->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfUserCourier = D('NfUserCourier');

        if ($NfUserCourier->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUserCourier->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfUserCourier = D('NfUserCourier');
        $ret = $NfUserCourier->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUserCourier->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfUserCourier = D('NfUserCourier');
        return $NfUserCourier->where('id=' . $id)->delete();
    }


    public function add_batch($data) {
        $NfUserCourier = D('NfUserCourier');
        if ($NfUserCourier->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfUserCourier = D('NfUserCourier');
        $data = [];
        $count = $NfUserCourier->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfUserCourier->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }

    public function set_name_by_uid($uid, $name) {
        $NfUserCourier = D('NfUserCourier');
        $NfCourier = D('NfCourier');
        $courier = $NfCourier->where('name="'.$name.'"')->find();
        if (!$courier) {
            return result(FALSE, '没有找到业务员信息');
        }
        $user_courier = $NfUserCourier->where('uid = ' . $uid)->find();
        if ($user_courier) {
            $ret = $NfUserCourier->where('id = ' . $user_courier['id'])->save(['courier_id'=>$courier['id'], 'courier_name' => $name]);
        } else {
            $ret = $NfUserCourier->add(['uid' => $uid, 'courier_name' => $name, 'courier_id'=>$courier['id']]);
        }
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUserCourier->getError());
        }
    }

    public function get_by_uids($uids) {
        $NfUserCourier = D('NfUserCourier');
        $user_couriers = $NfUserCourier->where('uid in (' . join(',', $uids) . ')')->select();
        return $user_couriers;
    }
}