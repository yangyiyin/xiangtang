<?php
/**
 * Created by newModule.
 * Time: 2017-12-13 10:22:32
 */
namespace Common\Service;
class ItemTimelimitActivityService extends BaseService{
    public static $name = 'ItemTimelimitActivity';

    public function add_one($data) {
        $NfModel = D('Nf' . static::$name);
        $data['create_time'] = isset($data['create_time']) ? $data['create_time'] : current_date();
         if (!$NfModel->create($data)) {
            return result(FALSE, $NfModel->getError());
         }
        if ($NfModel->add()) {
            return result(TRUE, '', $NfModel->getLastInsID());
        } else {

            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['id'] = ['EQ', $id];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfModel = D('Nf' . static::$name);

        if ($NfModel->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfModel = D('Nf' . static::$name);
        $ret = $NfModel->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfModel = D('Nf' . static::$name);
        $ret = $NfModel->where('id=' . $id)->save(['deleted'=>static::$DELETED]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }


    public function add_batch($data) {
        $NfModel = D('Nf' . static::$name);
        if ($NfModel->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfModel = D('Nf' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $count = $NfModel->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfModel->where($where)->order($order)->page($page . ',' . static::$page_size)->select();
        }
        return [$data, $count];
    }

    public function get_by_iids($iids) {
        if (!check_num_ids($iids)) {
            return result(FALSE, 'iids不能为空');
        }
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['iid'] = ['in', $iids];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }

    public function get_by_iid($iid) {
        if (!check_num_ids([$iid])) {
            return result(FALSE, 'iid不能为空');
        }
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['iid'] = $iid;
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }

    public function cancel_timelimit_activity($iids) {
        if (!check_num_ids($iids)) {
            return false;
        }
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['iid'] = ['in', $iids];
        $ret = $NfModel->where($where)->save(['deleted'=>static::$DELETED]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_price_by_info($info, $is_min=1) {
        if (!$info) {
            return false;
        }
        $start = strtotime($info[0]['start_time']);
        $end = strtotime($info[0]['end_time']);
        $now = time();
        if ($now > $end || $now < $start) {
            return false;
        }

        if ($is_min) {
            $prices = result_to_array($info, 'price');
            return min($prices);
        } else {
            return result_to_map($info, 'sku_id');
        }


    }

}