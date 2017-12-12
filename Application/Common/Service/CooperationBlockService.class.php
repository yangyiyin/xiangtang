<?php
/**
 * Created by newModule.
 * Time: 2017-12-12 10:13:14
 */
namespace Common\Service;
class CooperationBlockService extends BaseService{
    public static $name = 'CooperationBlock';

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

    public function get_by_cids_type($cids, $type){
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['cids'] = ['in', $cids];
        $where['type'] = ['eq', $type];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }

    public function get_by_type($type){
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['type'] = ['eq', $type];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->select();
    }


    public function set_block($cids, $type){
        if (!isset(\Common\Model\NfCooperationBlockModel::$type_map[$type])) {
            return result(FALSE, '非法type');
        }
        $items = $this->get_by_cids_type($cids, $type);

        if ($items) {
            $exists = result_to_array($items, 'cid');
            $news = array_diff($cids, $exists);
            if ($news) {
                $data = [];
                foreach ($news as $cid) {
                    $data[] = ['cid'=>$cid, 'type'=>$type];
                }
                return $this->add_batch($data);
            }
        } else {
            $data = [];
            foreach ($cids as $cid) {
                $data[] = ['cid'=>$cid, 'type'=>$type];
            }
            return $this->add_batch($data);
        }
        return result(FALSE, '设置失败');

    }


    public function cancel_block($cids, $type){
        if (!isset(\Common\Model\NfCooperationBlockModel::$type_map[$type])) {
            return result(FALSE, '非法type');
        }
        $items = $this->get_by_cids_type($cids, $type);

        if ($items) {
            $NfModel = D('Nf' . static::$name);
            $where = [];
            $where['id'] = ['in', result_to_array($items)];

            $ret = $NfModel->where($where)->save(['deleted'=>static::$DELETED]);
            if ($ret) {
                return result(TRUE, '设置成功');
            }
        } else {

        }
        return result(FALSE, '设置失败');

    }


}