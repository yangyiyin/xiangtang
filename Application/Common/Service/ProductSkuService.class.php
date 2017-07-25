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

        if ($id = $NfProductSku->add($data)) {
            return result(TRUE, '', $id);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }


    public function add_batch($data) {
        $NfProductSku = D('NfProductSku');
        $ret =  $NfProductSku->addAll($data);
        if ($NfProductSku->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function get_info_by_id($id) {
        $NfProductSku = D('NfProductSku');
        return $NfProductSku->where('id = ' . $id)->find();
    }

    public function get_by_ids($ids) {
        $NfProductSku = D('NfProductSku');
        $where = [];
        $where['id'] = ['in', $ids];
        return $NfProductSku->where($where)->select();
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
        return $NfProductSku->where('pid in (' . join(',', $pids) . ')')->order('id asc')->select();

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



    public function add_stock_by_id($id, $modify_num) {//方法只支持单个sku
        $NfProductSku = D('NfProductSku');
        if ($NfProductSku->where('id = ' . $id)->setInc('num',$modify_num)){
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }

    public function minus_stock_by_id($id, $modify_num) {//方法只支持单个sku
        $NfProductSku = D('NfProductSku');
        if ($NfProductSku->where('id = ' . $id)->setDec('num',$modify_num)){
            $NfProductSku->where('id = ' . $id)->setInc('sold',$modify_num);
            return result(TRUE);
        } else {
            return result(FALSE, $NfProductSku->getError());
        }
    }

    public function del_by_pid($pid) {
        $NfProductSku = D('NfProductSku');
        return $NfProductSku->where('pid=' . $pid)->delete();
    }


    public function check_stock($skus_num) {
        if ($skus_num) {
            $SkuPropertyService = \Common\Service\SkuPropertyService::get_instance();
            $sku_ids = result_to_array($skus_num);
            $sku_props = $SkuPropertyService->get_by_sku_ids($sku_ids);
            $sku_props_map = $SkuPropertyService->get_sku_props_map($sku_props);
            foreach ($skus_num as $key => $_item) {

                //检测库存
                if ($_item['num'] < $_item['buy_num']) {
                    return result(FALSE, $_item['item']['title'] . $sku_props_map[$_item['id']] .'库存不足~');
                }
            }

        } else {
            return result(FALSE, '没有商品~');
        }
        return result(TRUE, '检测成功');
    }
}