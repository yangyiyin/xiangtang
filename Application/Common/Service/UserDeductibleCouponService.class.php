<?php
/**
 * Created by newModule.
 * Time: 2017-12-13 10:23:27
 */
namespace Common\Service;
class UserDeductibleCouponService extends BaseService{
    public static $name = 'UserDeductibleCoupon';

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

    public function get_by_uid($uid) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['uid'] = ['EQ', $uid];
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

    public function recover_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfModel = D('Nf' . static::$name);
        $ret = $NfModel->where('id=' . $id)->save(['deleted'=>static::$NOT_DELETED]);
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


    public function gain($cid, $title, $least, $deductible, $num) {
        $batch_data = [];
        for($i = 0; $i < $num; $i++) {
            $tmp = [];
            $tmp['cid'] = $cid;
            $tmp['title'] = $title;
            $tmp['code'] = $this->gain_code($i);
            $tmp['least'] = $least;
            $tmp['deductible'] = $deductible;
            $batch_data[] = $tmp;
        }
        return $this->add_batch($batch_data);
    }

    public function gain_code($i) {
        return 'hsyd'.time().$i;
    }

    public function get_count_by_id($id) {
        if (!check_num_ids([$id])) {
            return 0;
        }
        $NfModel = D('Nf' . static::$name);
        $where['cid'] = $id;
        return $NfModel->where($where)->count();
    }

    public function take_one($uid) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['uid'] = ['EQ', 0];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $info = $NfModel->where($where)->order('id desc')->find();
        if (!$info) {
            return result(FALSE, '领取失败,优惠券已被领完');
        }

        //领取
        $data = [];
        $data['uid'] = $uid;
        return $this->update_by_id($info['id'], $data);
    }
}