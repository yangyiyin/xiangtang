<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class UsersessionService extends BaseService{
    public static $page_size = 20;

    public function add_one($data) {
        $NfUserSession = D('NfUserSession');
        if ($NfUserSession->add($data)) {
            return result(TRUE, '', $NfUserSession->getLastInsID());
        } else {
            return result(FALSE, $NfUserSession->getError());
        }
    }

    public function get_info_by_uid($uid) {
        $NfUserSession = D('NfUserSession');
        return $NfUserSession->where('uid = ' . $uid)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfUserSession = D('NfUserSession');

        if ($NfUserSession->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUserSession->getError());
        }
    }


    public function update_by_uid($uid, $data) {

        if (!$uid) {
            return result(FALSE, 'uid不能为空');
        }

        $NfUserSession = D('NfUserSession');

        if ($NfUserSession->where('uid=' . $uid)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfUserSession = D('NfUserSession');
        $ret = $NfUserSession->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfUserSession->getError());
        }
    }

    public function decode_user_session($user_session) {
        $user_session_str = base64_decode($user_session);
        return explode('|', $user_session_str);

    }
    public function encode_user_session($uid) {
        $str = $uid . '|' . time() . '|' . mt_rand(0,9);
        $str = base64_encode($str);
        return $str;
    }

    public function add_session_by_uid($uid) {
        $data = [];
        $data['uid'] = $uid;
        $data['session'] = $this->encode_user_session($uid);
        $ret = $this->add_one($data);
        if ($ret->success) {
            return result(TRUE, '', $data['session']);
        } else {
            return $ret;
        }
    }

    public function update_session_by_uid($uid) {
        $data = [];
        $data['session'] = $this->encode_user_session($uid);
        $ret = $this->update_by_uid($uid, $data);
        if ($ret->success) {
            return result(TRUE, '', $data['session']);
        } else {
            return $ret;
        }
    }

}