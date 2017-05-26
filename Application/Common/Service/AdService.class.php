<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class AdService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfAd = D('NfAd');
        if ($NfAd->add($data)) {
            return result(TRUE, '', $NfAd->getLastInsID());
        } else {
            return result(FALSE, $NfAd->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfAd = D('NfAd');
        return $NfAd->where('id = ' . $id)->find();
    }

    public function get_by_ids($ids) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids异常');
        }
        $NfAd = D('NfAd');
        return $NfAd->where('id in ( ' . join(',', $ids) . ')')->select();

    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfAd = D('NfAd');

        if ($NfAd->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfAd->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfAd = D('NfAd');
        $ret = $NfAd->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfAd->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfAd = D('NfAd');
        $ret = $NfAd->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfAd->getError());
        }
    }


    public function add_batch($data) {
        $NfAd = D('NfAd');
        if ($NfAd->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfAd = D('NfAd');
        $data = [];
        $count = $NfAd->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfAd->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }


}