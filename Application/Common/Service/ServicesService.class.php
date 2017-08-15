<?php
/**
 * Created by newModule.
 * Time: 2017-06-01 17:33:07
 */
namespace Common\Service;
class ServicesService extends BaseService{
    public static $name = 'Services';

    public function add_one($data) {
        $NfModel = D('Nf' . static::$name);
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

    public function get_info_by_name($name) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['title'] = ['EQ', $name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

    public function get_by_ids($ids) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['id'] = ['in', $ids];
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

    public function get_by_where_all($where, $order = 'id desc') {
        $NfModel = D('Nf' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $count = $NfModel->where($where)->count();
        if ($count > 0) {
            $data = $NfModel->where($where)->order($order)->select();
        }
        return [$data, $count];
    }

    public function get_all_option($selected_id = '') {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];

        $all = $NfModel->where($where)->select();
        $options = '';
        if ($all) {
            foreach ($all as $_li) {
                if ($selected_id && $selected_id == $_li['id']) {
                    $options .= '<option selected="selected" value="'.$_li['id'].'">'.$_li['title'].'</option>';
                } else {
                    $options .= '<option value="'.$_li['id'].'">'.$_li['title'].'</option>';
                }
            }
        }
        return $options;
    }
}