<?php
/**
 * Created by newModule.
 * Time: 2017-08-08 20:55:34
 */
namespace Common\Service;
class AccountService extends BaseService{
    public static $name = 'Account';

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

    public function add_account($uid, $sum) {
        if (!$uid || !$sum) {
            return result(FALSE, '参数错误');
        }
        //查询是否存在该账户
        $NfModel = D('Nf' . static::$name);
        $where['uid'] = $uid;
        $ret = $NfModel->where($where)->find();
        
        if (!$ret) {
            //创建账户
            $data = [];
            $data['uid'] = $uid;
            $data['sum'] = $sum;
            return $this->add_one($data);
        } else {
            $ret_inc = $NfModel->where(['id'=>$ret['id']])->setInc('sum', $sum);
            if ($ret_inc) {
                return result(TRUE, '');
            } else {

                return result(FALSE, '网络繁忙~');
            }
        }
    }

    public function minus_account($uid, $sum) {
        if (!$uid || !$sum) {
            return result(FALSE, '参数错误');
        }
        //查询是否存在该账户
        $NfModel = D('Nf' . static::$name);
        $where['uid'] = $uid;
        $ret = $NfModel->where($where)->find();
        if (!$ret) {
            //创建账户
            return result(FALSE, '该账户不存在~');
        } else {
            if ($sum > $ret['sum']) {
                return result(FALSE, '账户余额不足~');
            }

            $ret_inc = $NfModel->where(['id'=>$ret['id']])->setDec('sum', $sum);
            if ($ret_inc) {
                return result(TRUE, '');
            } else {

                return result(FALSE, '网络繁忙~');
            }
        }
    }


    public function check_is_available($uid, $sum) {
        $info = $this->get_info_by_uid($uid);
        if (!$info) {
            return result(FALSE, '您还没有开通账户~');
        }

        if ($info['sum'] < $sum) {
            return result(FALSE, '余额不足~');
        }
        return result(TRUE, '');

    }

    public function pay($uid, $sum) {
        $info = $this->get_info_by_uid($uid);
        if (!$info) {
            return result(FALSE, '您还没有开通账户~');
        }

        if ($info['sum'] < $sum) {
            return result(FALSE, '余额不足~');
        }

        $data = [];
        $data['sum'] = $info['sum'] - $sum;
        return $this->update_by_id($info['id'], $data);

    }


    public function hold($uid, $hold_value) {
        $info = $this->get_info_by_uid($uid);
        if ($info) {
            $data = [];
            $data['sum'] = $info['sum'] - $hold_value;
            $data['hold'] = $info['hold'] + $hold_value;
            return $this->update_by_id($info['id'], $data);
        } else {
            return result(FALSE, '您还没有开通个人账户~');
        }
    }

    public function unhold($uid, $hold_value) {
        $info = $this->get_info_by_uid($uid);
        if ($info) {
            if ($info['hold'] < $hold_value) {
                return result(FALSE, '账户锁定余额不足以扣款,系统异常~');
            }
            $data = [];
            $data['hold'] = $info['hold'] - $hold_value;
            return $this->update_by_id($info['id'], $data);
        } else {
            return result(FALSE, '您还没有开通个人账户~');
        }
    }

}