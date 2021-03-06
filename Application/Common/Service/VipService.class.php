<?php
/**
 * Created by newModule.
 * Time: 2017-12-30 13:37:04
 */
namespace Common\Service;
class VipService extends BaseService{
    public static $name = 'Vip';
    public static $price_info_list = [
        ["id"=>1,'time'=>'12个月','months'=>'12','price'=>88,'price_month'=>'低至7.3元/月','price_old'=>'¥180元','remark'=>'特惠'],
        ['id'=>2,'time'=>'6个月','months'=>'6','price'=>49,'price_month'=>'低至8元/月','price_old'=>'¥90元'],
        ['id'=>3,'time'=>'1个月','months'=>'1','price'=>9,'price_month'=>'&nbsp;','price_old'=>'¥15元','active'=>true],
    ];

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

    public function get_info_by_uid($uid) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['uid'] = ['EQ', $uid];
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

    public function extend_days($uid, $days) {
        //查询
        $info = $this->get_info_by_uid($uid);
        $now = time();
        if ($info) {
            $end_time_int = strtotime($info['end_time']);
            //比较是否在期内
            if ($end_time_int >= $now) {
                $end_time_int_modify = $end_time_int + $days * 3600 * 24;
                $end_time = date('Y-m-d H:i:s', $end_time_int_modify);
                $ret = $this->update_by_id($info['id'], ['end_time' => $end_time]);
            } else {
                $end_time_int_modify = $now + $days * 3600 * 24;
                $end_time = date('Y-m-d H:i:s', $end_time_int_modify);

                $start_time = date('Y-m-d H:i:s', $now);
                $ret = $this->update_by_id($info['id'], ['start_time' => $start_time, 'end_time' => $end_time]);
            }

        } else {
            $end_time_int_modify = $now + $days * 3600 * 24;
            $end_time = date('Y-m-d H:i:s', $end_time_int_modify);
            $start_time = date('Y-m-d H:i:s', $now);

            $data = [];
            $data['uid'] = $uid;
            $data['start_time'] = $start_time;
            $data['end_time'] = $end_time;
            $ret = $this->add_one($data);
        }

        return $ret;
    }

    public function is_vip($uid) {
        $info = $this->get_info_by_uid($uid);
        if (!$info) {
            return result(FALSE, '您还不是vip,请联系客服开通');
        }
        $date = date('Y-m-d H:i:s');
        if ($info['start_time'] > $date ) {
            return result(FALSE, '您的vip未生效');
        }
        if ($info['end_time'] < $date) {
            return result(FALSE, '您的vip已过期');
        }

        if ($info['start_time'] <= $date && $info['end_time'] >= $date) {
            return result(TRUE, '');
        }

        return result(FALSE, '您的vip异常,请联系客服');
    }


}