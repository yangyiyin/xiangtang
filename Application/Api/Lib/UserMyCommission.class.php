<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class UserMyCommission extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $AccountLogService;
    public function init() {
        $this->AccountLogService = Service\AccountLogService::get_instance();
    }

    public function excute() {
        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];
        $where['uid'] = $this->uid;
        list($sum, $count) = $this->AccountLogService->get_totals($where);

        return result_json(TRUE, '', ['sum' => intval($sum)]);
    }
}