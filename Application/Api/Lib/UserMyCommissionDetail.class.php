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
class UserMyCommissionDetail extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $AccountLogService;
    public function init() {
        $this->AccountLogService = Service\AccountLogService::get_instance();
    }

    public function excute() {
        $page = I('get.p');
        $page = $page ? $page : 1;
        $where = [];
        $where['type'] = ['in', [\Common\Model\NfAccountLogModel::TYPE_INVITER_ADD, \Common\Model\NfAccountLogModel::TYPE_INVITER_MINUS, \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD, \Common\Model\NfAccountLogModel::TYPE_DEALER_MINUS]];
        $where['uid'] = $this->uid;
        list($list, $count) = $this->AccountLogService->get_by_where($where);
        $list = $this->convert_data($list);
        $has_more = has_more($count, $page, Service\AccountLogService::$page_size);
        $result = [];
        $result = ['detail' => $list, 'has_more' => $has_more];
        return result_json(TRUE, '', $result);
    }

    public function convert_data($list) {
        $data = [];
        if ($list) {
            $type_map = \Common\Model\NfAccountLogModel::$TYPE_MAP;
            foreach ($list as $key => $value) {
                $temp = [];
                $temp['sum'] = intval($value['sum']);
                $temp['info'] = isset($type_map[$value['type']]) ? $type_map[$value['type']] : '未知';
                $temp['create_time'] = $value['create_time'];
                $data[] = $temp;
            }
        }
        return $data;
    }
}