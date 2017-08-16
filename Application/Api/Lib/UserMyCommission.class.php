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
    private $AccountService;
    public function init() {
        $this->AccountService = Service\AccountService::get_instance();
    }

    public function excute() {
        $info = $this->AccountService->get_info_by_uid($this->uid);
        $sum = isset($info['sum']) ? $info['sum'] : 0;
        return result_json(TRUE, '', ['sum' => intval($sum)]);
    }
}