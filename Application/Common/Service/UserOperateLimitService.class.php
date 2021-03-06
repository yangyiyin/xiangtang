<?php
/**
 * Created by newModule.
 * Time: 2017-08-08 20:55:34
 */
namespace Common\Service;
class UserOperateLimitService extends BaseService{
    public static $name = 'UserOperateLimit';

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

    public function get_info_by_uid_type($uid,$type) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['uid'] = ['EQ', $uid];
        $where['type'] = ['EQ', $type];
        $where['gmt'] = ['EQ', strtotime(date('Y-m-d'))];

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



    public function add_sum($uid, $type, $sum) {
        if (!$uid || !$type || !$sum) {
            return result(FALSE, '参数错误');
        }
        //查询是否存在该账户
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['uid'] = $uid;
        $where['type'] = $type;
        $where['gmt'] = strtotime(date('Y-m-d'));
        $ret = $NfModel->where($where)->find();
        if (!$ret) {
            //创建账户
            $data = [];
            $data['uid'] = $uid;
            $data['sum'] = $sum;
            $data['type'] = $type;
            $data['gmt'] = $where['gmt'];
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

}