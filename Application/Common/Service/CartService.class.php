<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class CartService extends BaseService{
    public static $page_size = 20;

    public function add_one($uid, $iid, $num, $sku_id = 0) {
        $NfCart = D('NfCart');
        //查询有没有数据
        $cart = $NfCart->where('uid = ' . $uid . ' and iid = ' . $iid . ' and sku_id= ' . $sku_id)->find();
        if ($cart) {
            //修改
            if (intval($num) == $cart['num']) {
                return result(TRUE, '网络繁忙');
            }
            $data = ['num' => intval($num)];
            if ($NfCart->where('id = ' . $cart['id'])->save($data)) {
                return result(TRUE, '', $cart['id']);
            } else {
                return result(FALSE, '网络繁忙');
            }
        } else {
            //新增
            $data = [];
            $data['uid'] = $uid;
            $data['iid'] = $iid;
            $data['num'] = $num;
            $data['sku_id'] = $sku_id;

            if ($NfCart->add($data)) {
                return result(TRUE, '', $NfCart->getLastInsID());
            } else {
                return result(FALSE, '网络繁忙');
            }
        }

    }

    public function get_info_by_id($id) {
        $NfCart = D('NfCart');
        return $NfCart->where('id = ' . $id)->find();
    }

    public function get_by_uid($uid) {
        $NfCart = D('NfCart');
        return $NfCart->where('uid = ' . $uid)->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfCart = D('NfCart');

        if ($NfCart->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCart->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfCart = D('NfCart');
        $ret = $NfCart->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCart->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return result(FALSE, 'id参数不合法');
        }
        $NfCart = D('NfCart');
        $ret = $NfCart->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfCart->getError());
        }
    }

    public function del_by_uid($uid) {
        if (!$uid) {
            return result(FALSE, '参数不合法');
        }

        $NfCart = D('NfCart');
        $ret = $NfCart->where('uid=' . $uid )->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }

    }


    public function del_by_uid_iids($uid, $iids) {
        if (!$uid || !check_num_ids($iids)) {
            return result(FALSE, '参数不合法');
        }

        $NfCart = D('NfCart');
        $ret = $NfCart->where('uid=' . $uid . ' and iid in (' . join(',', $iids) . ')')->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }

    }

    public function del_by_uid_iids_skuids($uid, $iids, $sku_ids) {
        if (!$uid || !check_num_ids($iids) || !check_num_ids($sku_ids)) {
            return result(FALSE, '参数不合法');
        }

        $NfCart = D('NfCart');
        $ret = $NfCart->where('uid=' . $uid . ' and iid in (' . join(',', $iids) . ')' . ' and sku_id in (' . join(',', $sku_ids) . ')')->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }

    }


    public function add_batch($data) {
        $NfCart = D('NfCart');
        if ($NfCart->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


}