<?php
/**
 * Created by newModule.
 * Time: 2018-01-02 09:28:12
 */
namespace Common\Service;
class PageBaseService extends BaseService{
    const pick_code_fightgroup = '01';
    const pick_code_praise = '02';
    const pick_code_sign = '03';
    const pick_code_quick_buy = '04';
    const pick_code_cutprice = '05';

    const pick_status_init = 0;
    const pick_status_verified = 1;
    const pick_status_completed = 2;
    public function get_by_phone_pick_code($phone, $pick_code) {
        $NfModel = D('Nf' . static::$name);
        $where = [];
        $where['phone'] = ['EQ', $phone];
        $where['pick_code'] = ['EQ', $pick_code];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $NfModel->where($where)->find();
    }



}