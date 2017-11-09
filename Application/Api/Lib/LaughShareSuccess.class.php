<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughShareSuccess extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {


        //查询是否可以投稿
        $UserOperateLimitService = \Common\Service\UserOperateLimitService::get_instance();
        $info = $UserOperateLimitService->get_info_by_uid_type($this->uid, \Common\Model\NfUserOperateLimitModel::TYPE_SHARE);

        if ($info && $info['sum'] > 2) {
            return result_json(TRUE, '分享成功,但是没有加积分,今天已累计3次');
        } else {
            $UserOperateLimitService->add_sum($this->uid, \Common\Model\NfUserOperateLimitModel::TYPE_SHARE, 1);
        }

        $AccountLogService = \Common\Service\AccountLogService::get_instance();
        $account_data = [];
        $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_SHARE;
        $account_data['sum'] = \Common\Model\NfAccountLogModel::$TYPE_VALUE_MAP[\Common\Model\NfAccountLogModel::TYPE_SHARE];
        $account_data['oid'] = 0;
        $account_data['uid'] = $this->uid;
        $account_data['pay_no'] = 0;
        $AccountLogService->add_one($account_data);

        $AccountService = \Common\Service\AccountService::get_instance();
        $AccountService->add_account($this->uid, $account_data['sum']);


        return result_json(TRUE, '分享成功');
    }

}