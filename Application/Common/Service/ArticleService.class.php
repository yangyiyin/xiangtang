<?php
/**
 * Created by newModule.
 * Time: 2017-06-08 12:20:24
 */
namespace Common\Service;
class ArticleService extends BaseService{
    public static $name = 'Article';

    public static $page_size = 10;
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
        //echo $NfModel->getLastSql();
        if ($count > 0) {
            $data = $NfModel->where($where)->order($order)->page($page . ',' . static::$page_size)->select();

        }
        return [$data, $count];
    }

    //往前多取一条
    public function get_by_where_with_pre_one($where, $order = 'id desc', $page = 1) {
        $NfModel = D('Nf' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $count = $NfModel->where($where)->order($order)->count();
        if ($count > 0) {
            if ($page == 1) {
                $limit = '0,' . static::$page_size;
            } else {
                $limit = (($page - 1) * static::$page_size - 1). ',' . (static::$page_size + 1);
            }
            $data = $NfModel->where($where)->order($order)->limit($limit)->select();
        }
        return [$data, $count];
    }


    public function get_pre_next($id, $type) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['type'] = ['eq', $type];
        $where['id'] = ['lt', $id];
        $where['deleted'] = ['eq', static::$NOT_DELETED];
        $pre = $NfModel->order('id desc')->where($where)->find();
        $where['id'] = ['gt', $id];
        $next = $NfModel->order('id asc')->where($where)->find();
        return [$pre, $next];
    }

    public function get_about() {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['type'] = ['EQ', $NfModel::TYPE_ABOUT];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

    public function get_contact() {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['type'] = ['EQ', $NfModel::TYPE_CONTACT];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

}