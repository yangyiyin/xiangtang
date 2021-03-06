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
        $where = [];
        $where['id'] = $id;
        $where['deleted'] = 0;

        return $NfUser->where($where)->find();
    }


    public function get_by_ids($ids) {
        $NfUser = D('NfUser');
        $where = [];
        $where['uid'] = ['in',$ids];
        return $NfUser->where($where)->select();
    }


    public function get_info_by_openid($id) {
        $NfUser = D('NfUser');
        $where = ['openid' => $id];
        $where['deleted'] = 0;
        return $NfUser->where($where)->find();
    }

    public function get_by_tel($tel, $status = 1) {
        $NfUser = D('NfUser');
        return $NfUser->where('user_tel = ' . $tel . ' and status = ' . $status)->find();
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
            $info['wechat_user_info'] = $info['wechat_user_info'] ? json_decode($info['wechat_user_info'],true) : [];
            return result(TRUE, '', $info);
        } else {
            if (!$info) return result(FALSE, '该账号未开通',1);

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


    public function be_inviter($ids) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $ids) .')')->save(['is_inviter'=>\Common\Model\NfUserModel::IS_INVITER_YES]);
        if ($ret) {
            //生成邀请码
            $UserInviterCodeService = \Common\Service\UserInviterCodeService::get_instance();
            $data = [];
            foreach ($ids as $uid) {
                $code = $this->get_inviter_code($uid);
                $data[] = ['uid'=>$uid, 'code'=>$code];
            }
            $UserInviterCodeService->add_batch($data);
            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function can_be_inviter($uid) {
        $info = $this->get_info_by_id($uid);
        if ($info['verify_status'] != \Common\Model\NfUserModel::VERIFY_STATUS_OK) {
            return result(FALSE, '没有认证为残疾人,不能成为分佣者');
        }

        if ($info['is_inviter'] != \Common\Model\NfUserModel::IS_INVITER_SUBMIT) {
            return result(FALSE, '当前分佣者状态不是提交状态,不能通过成为分佣者');
        }

        return result(TRUE);
    }


    public function nbe_inviter($ids) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'uids为空~');
        }
        $NfUser = D('NfUser');
        $ret = $NfUser->where('id in ('. join(',', $ids) .')')->save(['is_inviter'=>\Common\Model\NfUserModel::IS_INVITER_SUBMIT]);
        if ($ret) {

            $UserInviterCodeService = \Common\Service\UserInviterCodeService::get_instance();
            $UserInviterCodeService->del_by_uids($ids);

            return result(TRUE);
        } else {
            return result(FALSE, $NfUser->getError());
        }
    }

    public function can_nbe_inviter($uid) {
        $info = $this->get_info_by_id($uid);

        if ($info['is_inviter'] != \Common\Model\NfUserModel::IS_INVITER_YES) {
            return result(FALSE, '当前用户不是分佣者,退回无效');
        }

        return result(TRUE);
    }

    public function get_inviter_code($uid) {
        return mt_rand(10,99) . $uid;
    }

    public function is_dealer($type) {
        return $type == \Common\Model\NfUserModel::TYPE_DEALER;
    }
    public function is_normal($type) {
        return $type == \Common\Model\NfUserModel::TYPE_NORMAL;
    }
}