<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class OrderService extends BaseService{
    public static $page_size = 20;

    public static $order_steps = ['submit', 'paying', 'pay', 'send', 'stock_out', 'complete', 'cancel'];
    public function add_one($data) {
        $NfOrder = D('NfOrder');
        $data['status'] = isset($data['status']) ? $data['status'] : \Common\Model\NfOrderModel::STATUS_SUBMIT;
        if ($NfOrder->add($data)) {
            return result(TRUE, '', $NfOrder->getLastInsID());
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id) {
        $NfOrder = D('NfOrder');
        return $NfOrder->where('id = ' . $id)->find();
    }
    public function get_by_ids($ids) {
        $NfOrder = D('NfOrder');
        return $NfOrder->where('id in (' . join(',', $ids) . ')')->select();
    }
    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfOrder = D('NfOrder');

        if ($NfOrder->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~~');
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfOrder = D('NfOrder');
        $ret = $NfOrder->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfOrder->getError());
        }
    }

    public function add_batch($data) {
        $NfOrder = D('NfOrder');
        if ($NfOrder->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfOrder = D('NfOrder');
        $data = [];
        $count = $NfOrder->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfOrder->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }

    public function get_factory_type () {
        return \Common\Model\NfOrderModel::TYPE_ORDER_FACTORY;
    }
    public function get_meituan_type () {
        return \Common\Model\NfOrderModel::TYPE_ORDER_MEITUAN;
    }
    public function get_shop_type () {
        return \Common\Model\NfOrderModel::TYPE_ORDER_SHOP;
    }
    public function get_people_type () {
        return \Common\Model\NfOrderModel::TYPE_ORDER_PEOPLE;
    }

    public function get_type_txt($type) {
        return \Common\Model\NfOrderModel::$type_map[$type];
    }
    public function get_status_txt($status) {
        return \Common\Model\NfOrderModel::$status_map[$status];
    }

    public function process($order_id, $step, $uid = '') {
        $order = $this->get_info_by_id($order_id);
        if (!$order) {
            return result(FALSE, '没有找到订单记录~');
        }
        if ($uid && $order['uid'] != $uid) {
            return result(FALSE, '没有找到订单记录~');
        }
        if (!in_array($step, self::$order_steps)) {
            return result(FALSE, '非法操作!');
        }
        if (!method_exists($this, $step)) {
            return result(FALSE, '非法操作!');
        }
        return $this->$step($order);
    }

    public function batch_process($order_ids, $step) {
        $orders = $this->get_by_ids($order_ids);
        if (!$orders) {
            return result(FALSE, '没有找到订单记录~');
        }
        if (!in_array($step, self::$order_steps)) {
            return result(FALSE, '非法操作!');
        }
        $step = 'batch_' . $step;
        if (!method_exists($this, $step)) {
            return result(FALSE, '非法操作!');
        }
        return $this->$step($orders);
    }

    public function send($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_PAY) {
            return result(FALSE, '订单状态不是已付款状态,不能发货操作!');
        }

        return $this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_SENDING]);
    }

    public function batch_send($orders) {
        foreach ($orders as $order) {
            if ($order['status'] != \Common\Model\NfOrderModel::STATUS_PAY) {
                return result(FALSE, '订单id为'.$order['id'].',状态不是已付款状态,不能发货操作!');
            }
        }
        $order_ids = result_to_array($orders);
        return $this->update_by_ids($order_ids, ['status'=>\Common\Model\NfOrderModel::STATUS_SENDING]);
    }

    public function paying($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            return result(FALSE, '当前订单状态不能支付操作!');
        }

        return $this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_PAYING]);
    }

    public function payed($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_PAYING && $order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            return result(FALSE, '当前订单状态还不能支付成功操作!');
        }

        return $this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_PAY]);
    }

    public function complete($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_SENDING && $order['status'] != \Common\Model\NfOrderModel::STATUS_STOCK_OUT) {
            return result(FALSE, '当前订单状态还不能确认操作!');
        }

        return $this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_DONE]);
    }

    public function stock_out($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            return result(FALSE, '订单状态不是已提交状态,不能出库操作!');
        }

        $OrderItemService = \Common\Service\OrderItemService::get_instance();
        $order_items = $OrderItemService->get_by_order_id($order['id']);
        $productNoSkuService = \Common\Service\ProductNoSkuService::get_instance();
        $pids = result_to_array($order_items, 'pid');
        $productNoSkus = $productNoSkuService->get_by_pids($pids);
        $productNoSkus_map = result_to_complex_map($productNoSkus, 'pid');
        //这里因为一个pid只有一个iid,所以不需要考虑一个订单有多个iid属于同一pid的情况
        $productSkuService = \Common\Service\ProductSkuService::get_instance();
        $stockInOutLogService = \Common\Service\StockInOutLogService::get_instance();
        foreach ($order_items as $_item) {
            if (isset($productNoSkus_map[$_item['pid']])) {
                $no = '';
                foreach ($productNoSkus_map[$_item['pid']] as $_no_sku) {
                    if ($_no_sku['num'] >= $_item['num']) {
                        $no = $_no_sku['product_no'];
                        $modify_num = $_item['num'];
                        break;
                    }
                }
                if (!$no) {
                    return result(FALSE, 'pid:' . $_item['pid'] . ', 库存不足!');
                }
            } else {
                return result(FALSE, 'pid:' . $_item['pid'] . ',没有批号库存信息!');
            }
        }
        foreach ($order_items as $_item) {
            $no = '';
            foreach ($productNoSkus_map[$_item['pid']] as $_no_sku) {
                if ($_no_sku['num'] >= $_item['num']) {
                    $no = $_no_sku['product_no'];
                    $modify_num = $_item['num'];
                    break;
                }
            }
            //出库
            $modify_num = $_item['num'];

            $ret = $productNoSkuService->minus_stock_no($no, $modify_num);
            if (!$ret->success) {
                return result(FALSE, '出库扣批号库存失败,请联系技术!');
            }

            //减总库存
            $ret = $productSkuService->minus_stock_by_pid($_item['pid'], $modify_num);
            if (!$ret->success) {
                return result(FALSE, '出库扣总库存失败,请联系技术!');
            }
            //增加出入库记录
            $stockInOutLogService->add_out(['pid'=>$_item['pid'], 'product_no'=>$no, 'info'=>'pid:'. $_item['pid'] .',批号:'. $no.',出库数量:'.$modify_num]);

        }
        if (!$this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_STOCK_OUT])) {
            return result(FALSE, '出库失败,请联系技术!');
        }
        return result('TRUE');

    }
    public function cancel($order) {
        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            return result(FALSE, '订单状态不是已提交状态,不能取消操作!');
        }

        return $this->update_by_id($order['id'], ['status'=>\Common\Model\NfOrderModel::STATUS_CANCEL]);
    }

    public function create_by_pre_order_id($pre_order_id, $uid, $extra) {

        $OrderPreService = \Common\Service\OrderPreService::get_instance();
        $OrderItemPreService = \Common\Service\OrderItemPreService::get_instance();
        $order_pre = $OrderPreService->get_info_by_id($pre_order_id);
        $order_pre_items = $OrderItemPreService->get_by_oid($pre_order_id);

        if (!$order_pre || !$order_pre_items) {
            return result(FALSE, '订单信息异常~');
        }

        $iids = result_to_array($order_pre_items, 'iid');
        $ItemService = \Common\Service\ItemService::get_instance();
        $items = $ItemService->get_by_ids($iids);
        if (!$items || (count($items) != count($order_pre_items))) {
            return result(FALSE, '订单商品异常~');
        }
        $order_pre_items_map = result_to_map($order_pre_items, 'iid');
        foreach ($items as $key => $_item) {
            $items[$key]['num'] = $order_pre_items_map[$_item['id']]['num'];
            $items[$key]['price'] = $order_pre_items_map[$_item['id']]['price'];
            $items[$key]['num'] = $order_pre_items_map[$_item['id']]['num'];
            $items[$key]['sum'] = $order_pre_items_map[$_item['id']]['sum'];
        }
        $ret = $ItemService->check_status_stock($items);
        if (!$ret->success) {
            return result(FALSE, $ret->message);
        }

        $userService = \Common\Service\UserService::get_instance();
        $user_info = $userService->get_info_by_id($uid);

        $data_order = [];
        $data_order['order_no'] = $this->get_order_no($uid);
        $data_order['uid'] = $uid;
        $data_order['sum'] = $order_pre['sum'];
        $data_order['num'] = $order_pre['num'];
        $data_order['type'] = $user_info['type'];//用户type和订单type保持一致
        $data_order['receiving_address'] = $extra['address'];
        $data_order['receiving_name'] = $extra['name'];
        $data_order['receiving_tel'] = $extra['tel'];
        $data_order['create_time'] = current_date();
        $ret = $this->add_one($data_order);
        if (!$ret->success) {
            return result(FALSE, '创建订单失败');
        }
        //插入order_item
        $order_id = $ret->data;
        $data_order_items = [];
        foreach ($order_pre_items as $_item) {
            $temp = [];
            $temp['order_id'] = $order_id;
            $temp['pid'] = $_item['pid'];
            $temp['iid'] = $_item['iid'];
            $temp['num'] = $_item['num'];
            $temp['sum'] = $_item['sum'];
            $temp['price'] = $_item['price'];
            $data_order_items[] = $temp;
        }
        $OrderItemService = \Common\Service\OrderItemService::get_instance();
        $ret = $OrderItemService->add_batch($data_order_items);
        if (!$ret->success) {
            return result(FALSE, '创建订单详情失败');
        }

        //插入快照
        $snap = [];
        foreach ($items as $key => $_item) {
            $_item['id'] = (int) $_item['id'];
            $_item['pid'] = (int) $_item['pid'];
            $_item['price'] = (int) $_item['price'];
            $_item['img'] = item_img(get_cover($_item['img'], 'path'));
            $snap[] = convert_obj($_item, 'id=iid,pid,title,img,desc,unit_desc,price,num,sum');
        }
        $snap_content = json_encode($snap);
        $OrderSnapshotService = \Common\Service\OrderSnapshotService::get_instance();
        $ret = $OrderSnapshotService->add_one(['order_id'=>$order_id, 'content'=>$snap_content]);

        if (!$ret->success) {
            return result(FALSE, '创建订单快照失败~');
        }
        //删除购物车
        $CartService = \Common\Service\CartService::get_instance();
        $CartService->del_by_uid_iids($uid, $iids);

        return result(TRUE, '创建成功', $order_id);
    }

    public function get_order_no($uid) {
        return 'O'.$uid.getMillisecond().mt_rand(0,9).mt_rand(0,9);
    }

    public function is_available_status($status) {
        return isset(\Common\Model\NfOrderModel::$status_map[$status]);
    }

    public function is_available_paying($order_id, $uid) {
        $order = $this->get_info_by_id($order_id);
        if (!$order) {
            return result(FALSE, '订单不存在');
        }

        if ($order['uid'] != $uid) {
            return result(FALSE, '订单不存在');
        }

        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            $this->get_status_txt();
            $status_desc = isset(\Common\Model\NfOrderModel::$status_map[$order['status']]) ? \Common\Model\NfOrderModel::$status_map[$order['status']] : '未知';
            return result(FALSE, '订单状态为:' . $status_desc);
        }

        return result(TRUE, '', $order);

    }


    public function is_available_payed($order_id, $uid) {
        $order = $this->get_info_by_id($order_id);
        if (!$order) {
            return result(FALSE, '订单不存在');
        }

        if ($order['uid'] != $uid) {
            return result(FALSE, '订单不存在');
        }

        if ($order['status'] != \Common\Model\NfOrderModel::STATUS_PAYING && $order['status'] != \Common\Model\NfOrderModel::STATUS_SUBMIT) {
            $this->get_status_txt();
            $status_desc = isset(\Common\Model\NfOrderModel::$status_map[$order['status']]) ? \Common\Model\NfOrderModel::$status_map[$order['status']] : '未知';
            return result(FALSE, '订单状态为:' . $status_desc);
        }

        return result(TRUE, '', $order);

    }

    public function get_to_pay($order) {
        return ($this->get_people_type() == $order['type']) && ($order['status'] == \Common\Model\NfOrderModel::STATUS_SUBMIT);
    }

    public function get_my_status() {
        return [\Common\Model\NfOrderModel::STATUS_SUBMIT, \Common\Model\NfOrderModel::STATUS_RECEIVED, \Common\Model\NfOrderModel::STATUS_SENDING];
    }
    public function get_count_by_where($where) {
        $NfOrder = D('NfOrder');
        $count = $NfOrder->where($where)->count();
        return $count;
    }

}