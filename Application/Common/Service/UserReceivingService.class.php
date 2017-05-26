<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class UserReceivingService extends BaseService
{
    public static $page_size = 20;

    public function add_one($data)
    {
        $NfUserReceiving = D('NfUserReceiving');
        if ($NfUserReceiving->add($data)) {
            return result(TRUE, '', $NfUserReceiving->getLastInsID());
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id)
    {
        $NfUserReceiving = D('NfUserReceiving');
        return $NfUserReceiving->where('id = ' . $id)->find();
    }

    public function get_by_uid($uid)
    {
        $NfUserReceiving = D('NfUserReceiving');
        return $NfUserReceiving->where('uid = ' . $uid)->order('id desc')->select();
    }

    public function get_by_uid_default($uid)
    {
        $NfUserReceiving = D('NfUserReceiving');
        return $NfUserReceiving->where('uid = ' . $uid . ' and is_default = 1')->find();
    }

    public function update_by_id($id, $data)
    {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfUserReceiving = D('NfUserReceiving');

        if ($NfUserReceiving->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }


    public function update_by_uid($uid, $data)
    {

        if (!$uid) {
            return result(FALSE, 'uid不能为空');
        }

        $NfUserReceiving = D('NfUserReceiving');

        if ($NfUserReceiving->where('uid=' . $uid)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function del_by_id($id)
    {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfUserReceiving = D('NfUserReceiving');
        $ret = $NfUserReceiving->where('id=' . $id)->delete();
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function set_default_by_id($id, $uid) {
        $this->update_by_uid($uid, ['is_default' => 0]);
        $ret = $this->update_by_id($id, ['is_default' => 1]);
        if ($ret->success) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }






}