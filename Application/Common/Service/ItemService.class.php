<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ItemService extends BaseService{
    public static $page_size = 20;
    public function add_one($data) {
        $NfItem = D('NfItem');
        $data['status'] = isset($data['status']) ? $data['status'] : \Common\Model\NfItemModel::STATUS_NORAML;
        $data['create_time'] = isset($data['create_time']) ? $data['create_time'] : current_date();
        if (!$NfItem->create($data)) {
            return result(FALSE, $NfItem->getError());
        }

        if ($NfItem->add()) {
            return result(TRUE, '', $NfItem->getLastInsID());
        } else {
            return result(FALSE, $NfItem->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfItem = D('NfItem');
        return $NfItem->where('id = ' . $id)->find();
    }

    public function get_by_ids($ids) {
        if (!check_num_ids($ids)) {
            return false;
        }
        $NfItem = D('NfItem');
        return $NfItem->where('id in (' . join(',', $ids) . ')')->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfItem = D('NfItem');

        if (!$NfItem->create($data)) {
            return result(FALSE, $NfItem->getError());
        }

        if ($NfItem->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙');
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfItem = D('NfItem');
        $ret = $NfItem->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfItem->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfItem = D('NfItem');
        return $NfItem->where('id=' . $id)->delete();
    }

    public function get_by_pids($pids) {
        if (!check_num_ids($pids)) {
            return false;
        }
        $NfItem = D('NfItem');
        return $NfItem->where('pid in (' . join(',', $pids) . ')')->select();

    }

    public function add_batch($data) {
        $NfItem = D('NfItem');
        if ($NfItem->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function off_shelf($ids) {
        return $this->update_by_ids($ids, ['status'=>\Common\Model\NfItemModel::STATUS_DELETE]);
    }

    public function on_shelf($ids) {
        return $this->update_by_ids($ids, ['status'=>\Common\Model\NfItemModel::STATUS_NORAML]);
    }

    public function approve($ids) {
        return $this->update_by_ids($ids, ['status'=>\Common\Model\NfItemModel::STATUS_NORAML]);
    }

    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfItem = D('NfItem');
        $data = [];
        $count = $NfItem->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfItem->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }

        return [$data, $count];
    }

    public function get_status_txt($status) {
        $status = isset(\Common\Model\NfItemModel::$status_map[$status]) ? \Common\Model\NfItemModel::$status_map[$status] : '未知';
        return $status;
    }

    public function check_status_stock($items_num) {
        if ($items_num) {
            $ProductSkuService = \Common\Service\ProductSkuService::get_instance();
            $pids = result_to_array($items_num, 'pid');
            $skus = $ProductSkuService->get_by_pids($pids);
            $skus_pid_map = result_to_map($skus, 'pid');//一个pid只有一个sku
            if (!$skus_pid_map) {
                return result_json(FALSE, '库存异常~');
            }

            foreach ($items_num as $key => $_item) {
                if ($_item['status'] != \Common\Model\NfItemModel::STATUS_NORAML) {
                    return result(FALSE, $_item['title'] . '状态为' . $this->get_status_txt($_item['status']));
                }

                //检测库存
                if (!isset($skus_pid_map[$_item['pid']]['num'])) {
                    return result(FALSE, $_item['title'] . '库存异常~');
                }

                if (!isset($_item['num']) || !$_item['num']) {
                    return result(FALSE, $_item['title'] . '购买数量未知~');
                }

                if ($skus_pid_map[$_item['pid']]['num'] < $_item['num']) {
                    return result(FALSE, $_item['title'] . '库存不足~');
                }
            }

        } else {
            return result(FALSE, '没有商品~');
        }
        return result(TRUE, '检测成功');
    }
}