<?php
/**
 * Created by newModule.
 * Time: 2018-01-02 09:28:48
 */
namespace Common\Service;
class PageCutpriceService extends PageBaseService{
    public static $name = 'PageCutprice';

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

    public function get_by_page_id($id, $is_master=0) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['page_id'] = ['EQ', $id];
        if ($is_master) {
            $where['pid'] = ['EQ', 0];
        }
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }

    public function get_by_uid_page_id($uid, $id, $pid=0) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['page_id'] = ['EQ', $id];
        $where['uid'] = ['EQ', $uid];
        $where['pid'] = ['EQ', $pid];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

    public function get_by_uid_page_id_all($uid, $id) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['page_id'] = ['EQ', $id];
        $where['uid'] = ['EQ', $uid];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }

    public function get_by_pid_page_id_all($uid, $id) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['page_id'] = ['EQ', $id];
        $where['pid'] = ['EQ', $uid];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
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


}