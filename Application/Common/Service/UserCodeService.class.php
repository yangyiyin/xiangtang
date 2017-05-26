<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class UserCodeService extends BaseService{
    public static $page_size = 20;

    public function add_one($tel) {
        $NfUserCode = D('NfUserCode');
        $data = [];
        $data['tel'] = $tel;
        $data['code'] = mt_rand(100000, 999999);
        $data['create_time'] = current_date();
        if ($NfUserCode->add($data)) {
            return result(TRUE, '', $data['code']);
        } else {
            return result(FALSE, '网络繁忙');
        }
    }

    public function get_info_by_tel($tel) {
        $NfUserCode = D('NfUserCode');
        return $NfUserCode->where('tel = ' . $tel . ' and status = 1')->order('id desc')->find();
    }

    public function check_code_by_tel($tel, $code) {
        $code_info = $this->get_info_by_tel($tel);
        if ($code_info && $code_info['code'] == $code) {
            return result(TRUE);
        } else {
            return result(FALSE, '验证码错误');
        }

    }

    public function disable_code_by_tel($tel) {
        $NfUserCode = D('NfUserCode');
        return $NfUserCode->where('tel=' . $tel)->save(['status' => 2]);
    }



}