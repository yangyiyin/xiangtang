<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ProductService extends BaseService{
    public static $page_size = 20;
    public function add_one($data) {
        $NfProduct = D('NfProduct');
        $ret = $NfProduct->add_by_data($data);
        if ($ret) {
            return result(TRUE, '', $NfProduct->getLastInsID());
        } else {
            return result(FALSE, $NfProduct->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfProduct = D('NfProduct');
        return $NfProduct->get_by_id($id);
    }

    public function get_by_ids($ids) {
        if (!check_num_ids($ids)) {
            return FALSE;
        }
        $NfProduct = D('NfProduct');
        return $NfProduct->where('id in ('. join(',', $ids) .')')->select();
    }

    public function update_by_id($id, $data) {
        $NfProduct = D('NfProduct');
        $ret = $NfProduct->update_by_id($id, $data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProduct->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfProduct = D('NfProduct');
        $ret = $NfProduct->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProduct->getError());
        }
    }

    public function off_shelf($ids) {
        return $this->update_by_ids($ids, ['status'=>\Common\Model\NfProductModel::STATUS_DELETE]);
    }

    public function on_shelf($ids) {
        return $this->update_by_ids($ids, ['status'=>\Common\Model\NfProductModel::STATUS_NORAML]);
    }

    public function del_by_id($id) {
        $NfProduct = D('NfProduct');
        return $NfProduct->del_by_id($id);
    }

    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfProduct = D('NfProduct');
        $data = [];
        $count = $NfProduct->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfProduct->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }

        return [$data, $count];
    }

    public function get_status_txt($status) {
        return \Common\Model\NfProductModel::$status_map[$status];
    }


}