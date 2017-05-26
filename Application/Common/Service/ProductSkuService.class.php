<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ProductSkuService extends BaseService{

    public function add_one($data) {
        $NfProductSku = D('NfProductSku');
        if (!$NfProductSku->create($data)) {
            return result(FALSE, $NfProductSku->getError());
        }

        if ($NfProductSku->add($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfProductSku = D('NfProductSku');
        return $NfProductSku->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfProductSku = D('NfProductSku');

        if (!$NfProductSku->create($data)) {
            return result(FALSE, $NfProductSku->getError());
        }

        if ($NfProductSku->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfProductSku = D('NfProductSku');
        return $NfProductSku->where('id=' . $id)->delete();
    }

    public function get_by_pids($pids) {
        if (!check_num_ids($pids)) {
            return false;
        }
        $NfProductSku = D('NfProductSku');
        return $NfProductSku->where('pid in (' . join(',', $pids) . ')')->select();

    }

    public function add_stock_by_pid($pid, $modify_num) {//方法只支持单个sku
        $NfProductSku = D('NfProductSku');
        if ($NfProductSku->where('pid = ' . $pid)->setInc('num',$modify_num)){
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }

    public function minus_stock_by_pid($pid, $modify_num) {//方法只支持单个sku
        $NfProductSku = D('NfProductSku');
        if ($NfProductSku->where('pid = ' . $pid)->setDec('num',$modify_num)){
            $NfProductSku->where('pid = ' . $pid)->setInc('sold',$modify_num);
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }
}