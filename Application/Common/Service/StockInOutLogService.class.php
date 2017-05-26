<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class StockInOutLogService extends BaseService{

    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    public static $type_map = [1=>'入库', 2=>'出库'];
    public static $page_size = 20;
    public function add_in($data) {
        $data['type'] = self::TYPE_IN;
        $data['create_time'] = current_date();
        $NfStockInOutLog = D('NfStockInOutLog');
        if ($NfStockInOutLog->add($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfStockInOutLog->getError());
        }
    }

    public function add_out($data) {
        $data['type'] = self::TYPE_OUT;
        $data['create_time'] = current_date();
        $NfStockInOutLog = D('NfStockInOutLog');
        if ($NfStockInOutLog->add($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfStockInOutLog->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfStockInOutLog = D('NfStockInOutLog');
        return $NfStockInOutLog->where('id = ' . $id)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfStockInOutLog = D('NfStockInOutLog');

        if (!$NfStockInOutLog->create($data)) {
            return result(FALSE, $NfStockInOutLog->getError());
        }

        if ($NfStockInOutLog->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfStockInOutLog->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfStockInOutLog = D('NfStockInOutLog');
        return $NfStockInOutLog->where('id=' . $id)->delete();
    }

    public function get_by_pids($pids) {
        if (!check_num_ids($pids)) {
            return false;
        }
        $NfStockInOutLog = D('NfStockInOutLog');
        return $NfStockInOutLog->where('pid in (' . join(',', $pids) . ')')->select();

    }

    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfStockInOutLog = D('NfStockInOutLog');
        $data = [];
        $count = $NfStockInOutLog->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfStockInOutLog->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }

        return [$data, $count];
    }

    public function get_type_txt($type) {
        return self::$type_map[$type];
    }

}