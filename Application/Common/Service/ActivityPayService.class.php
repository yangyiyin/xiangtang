<?php
/**
 * Created by newModule.
 * Time: 2017-12-26 14:07:31
 */
namespace Common\Service;
class ActivityPayService extends BaseService{
    public static $name = 'ActivityPay';

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
//        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }

    public function get_by_ids($ids) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['id'] = ['in', $ids];
//        $where['deleted'] = ['EQ', static::$NOT_DELETED];
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

//    public function del_by_id($id, $uid) {
//        if (!check_num_ids([$id])) {
//            return false;
//        }
//        $NfModel = D('Nf' . static::$name);
//        $where = [];
//        $where['id'] = ['eq', $id];
//        $where['uid'] = $uid;
//        $ret = $NfModel->where($where)->save(['deleted'=>static::$DELETED]);
//        if ($ret) {
//            return result(TRUE);
//        } else {
//            return result(FALSE, '网络繁忙~');
//        }
//    }


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
//        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $count = $NfModel->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfModel->where($where)->order($order)->page($page . ',' . static::$page_size)->select();
        }
        return [$data, $count];
    }

    public function gen_pay_no($uid, $pre='fg'){
        return $pre.'-'.time().'-'.$uid.'-'.mt_rand(11,99);
    }

    public function create_pay($activity_label, $page_id, $uid, $extra_uid) {
        //检验合法性
        $PageService = PageService::get_instance();
        $page_info = $PageService->get_info_by_id($page_id);
        if (!$page_info) {
            return false;
        }
        $tmp_data = json_decode($page_info['tmp_data'], TRUE);

        if (!$tmp_data || !isset($tmp_data['page']) || !$tmp_data['page']) {
            return false;
        }

        $is_legal = false;
        $price = 0;
        foreach ($tmp_data['page'] as $item) {
            if ($activity_label == 'fight_group' && $activity_label == $item['type']) {//检测通过
                $is_legal = true;
                //获取价格
                $price = $item['fight_group_price'];//todo 如果有团长价,则团长价
                break;
            }
        }
        if (!$is_legal) {
            return false;
        }

        //创建支付订单
        $NfModel = D('Nf' . static::$name);
        $data = [];
        $data['activity_id'] = $page_id;
        $data['uid'] = $uid;
        $data['extra_uid'] = $extra_uid;
        $data['sum'] = $price * 100;
        $data['pay_no'] = $this->gen_pay_no($uid, 'fg');
        $data['label'] = $activity_label;
        if ($NfModel->add($data)) {
            return $data;
        } else {
            return false;
        }
    }

}