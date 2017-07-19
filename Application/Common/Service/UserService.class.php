<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class UserService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfUser = D('NfUser');
        $data['status'] = isset($data['status']) ? $data['status'] : \Common\Model\NfUserModel::STATUS_VERIFY;
        $data['create_time'] = isset($data['create_time']) ? $data['create_time'] : current_date();

        //个人用户,直接通过
        $data['status'] = \Common\Model\NfUserModel::STATUS_NORAML;
        if (!$NfUser->create($data)) {
            return result(FALSE, $NfUser->getError());
        }

        if ($NfUser->add()) {
            return result(TRUE, '', $NfUser->getLastInsID());
        } else {
            return result(FALSE, '网络繁忙');
        }
    }

    public function get_info_by_id($id) {
        $NfUser = D('NfUser');
        return $NfUser->where('id = ' . $id)->find();
    }

    public function get_by_uids($ids) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfUser = D('NfUser');
        return $NfUser->where(['id'=>['in', $ids]])->select();
    }

    public function get_by_tel($tel, $status = 1) {
        $NfUser = D('NfUser');
        $where = [];
        $where[] = ['user_tel'=>['eq', $tel]];
        $where[] = ['status'=>['eq', $status]];

        return $NfUser->where($where)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfUser = D('NfUser');

        if (!$NfUser->create($data)) {
            return result(FALSE, $NfUser->getError());
        }

        if ($NfUser->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfUser = D('NfUser');
        return $NfUser->where('id=' . $id)->delete();
    }

    public function get_by_pids($pids) {
        if (!check_num_ids($pids)) {
            return false;
        }
        $NfUser = D('NfUser');
        return $NfUser->where('pid in (' . join(',', $pids) . ')')->select();

    }

    public function add_batch($data) {
        $NfUser = D('NfUser');
        if ($NfUser->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $NfUser = D('NfUser');
        $data = [];
        $count = $NfUser->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $NfUser->where($where)->order($order)->page($page . ',' . self::$page_size)->select();
        }
        return [$data, $count];
    }

    public function get_by_where_all($where, $order = 'id desc') {
        $NfUser = D('NfUser');
        $data = [];
        $count = $NfUser->where($where)->count();
        if ($count > 0) {
            $data = $NfUser->where($where)->order($order)->select();
        }
        return $data;
    }

    public function approve($uids) {
        if (!check_num_ids($uids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $uids) .')')->save(['status'=>\Common\Model\NfUserModel::STATUS_NORAML]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function forbid($uids) {
        if (!check_num_ids($uids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $uids) .')')->save(['status'=>\Common\Model\NfUserModel::STATUS_FORBID]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function approve_entity($uids) {
        if (!check_num_ids($uids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $uids) .')')->save(['verify_status'=>\Common\Model\NfUserModel::VERIFY_STATUS_OK]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function reject_entity($uids) {
        if (!check_num_ids($uids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $uids) .')')->save(['verify_status'=>\Common\Model\NfUserModel::VERIFY_STATUS_REJECT]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function get_status_txt($status) {
        return \Common\Model\NfUserModel::$status_map[$status];
    }

    public function get_type_txt($type) {
        return \Common\Model\NfUserModel::$type_desc_map[$type];
    }

    public function is_available($uid) {
        $info = $this->get_info_by_id($uid);
        if ($info && $info['status'] == \Common\Model\NfUserModel::STATUS_NORAML) {
            return result(TRUE, '', $info);
        } else {
            if (!$info) return result(FALSE, '该账号未开通');

            return result(FALSE, '该账号状态为' . \Common\Model\NfUserModel::$status_map[$info['status']]);
        }
    }

    public function check_tel_available($tel) {
        $NfUser = D('NfUser');
        if ($NfUser->where('user_tel='.$tel)->find()) {
            return result(FALSE, '该手机号已存在');
        } else {
            return result(TRUE);
        }
    }

    public function get_type_people () {
        return \Common\Model\NfUserModel::TYPE_PEOPLE;
    }

    public function get_verify_status_submit() {
        return \Common\Model\NfUserModel::VERIFY_STATUS_SUBMIT;
    }
}