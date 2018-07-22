<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Model;
use Common\Service;
class UserMyCommission extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $AccountService;
    public function init() {
        $this->AccountService = Service\AccountService::get_instance();
    }

    public function excute() {

        $page = I('get.p', 1);
        if (I('get.limit')) {
            Service\AccountService::$page_size = I('get.limit');
        }
        $where = [];
        //$where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_OUT_CASH_MINUS, \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];
        list($list, $count) = $this->AccountService->get_by_where($where);
        //$has_more = has_more($count, $page, Service\AccountService::$page_size);
        $result = ['list' => $list, 'count' => $count];
        return result_json(TRUE, '', $result);

    }
}