<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class CourierService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfCourier = D('NfCourier');
        $data['status'] = isset($data['status']) ? $data['status'] : \Common\Model\NfCourierModel::STATUS_NORAML;
        if ($NfCourier->add($data)) {
            return result(TRUE, '', $NfCourier->getLastInsID());
        } else {
            return result(FALSE, $NfCourier->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfCourier = D('NfCourier');
        return $NfCourier->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfCourier = D('NfCourier');

        if ($NfCourier->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCourier->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfCourier = D('NfCourier');
        $ret = $NfCourier->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCourier->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfCourier = D('NfCourier');
        $ret = $NfCourier->where('id=' . $id)->save(['status'=>\Common\Model\NfCourierModel::STATUS_FORBID]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCourier->getError());
        }
    }


    public function add_batch($data) {
        $NfCourier = D('NfCourier');
        if ($NfCourier->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfCourier = D('NfCourier');
        $data = [];
        $where['status'] = isset($where['status']) ? $where['status'] : $where['status'] = ['EQ', \Common\Model\NfCourierModel::STATUS_NORAML];
        $count = $NfCourier->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfCourier->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }


}