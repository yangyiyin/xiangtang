<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ProductNoSkuService extends BaseService{

    public function add_one($data) {
        $NfProductNoSku = D('NfProductNoSku');
        if (!$NfProductNoSku->create($data)) {
            return result(FALSE, $NfProductNoSku->getError());
        }

        if ($NfProductNoSku->add($data)) {
            return result(TRUE, '', $NfProductNoSku->getLastInsID());
        } else {
            return result(FALSE, $NfProductNoSku->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfProductNoSku = D('NfProductNoSku');
        return $NfProductNoSku->where('id = ' . $id)->find();
    }

    public function get_info_by_no($no) {
        $NfProductNoSku = D('NfProductNoSku');
        return $NfProductNoSku->where('product_no = ' . $no)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfProductNoSku = D('NfProductNoSku');

        if (!$NfProductNoSku->create($data)) {
            return result(FALSE, $NfProductNoSku->getError());
        }

        if ($NfProductNoSku->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductNoSku->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfProductNoSku = D('NfProductNoSku');
        return $NfProductNoSku->where('id=' . $id)->delete();
    }

    public function get_by_pids($pids) {
        if (!check_num_ids($pids)) {
            return false;
        }
        $NfProductNoSku = D('NfProductNoSku');
        return $NfProductNoSku->where('pid in (' . join(',', $pids) . ')')->order('id asc')->select();

    }

    public function add_stock_no($no, $modify_num) {
        $NfProductNoSku = D('NfProductNoSku');
        if ($NfProductNoSku->where('product_no = ' . $no)->setInc('num',$modify_num)){
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductNoSku->getError());
        }
    }

    public function minus_stock_no($no, $modify_num) {
        $NfProductNoSku = D('NfProductNoSku');
        if ($NfProductNoSku->where('product_no = ' . $no)->setDec('num',$modify_num)){
            $NfProductNoSku->where('product_no = ' . $no)->setInc('sold',$modify_num);
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductNoSku->getError());
        }
    }
}